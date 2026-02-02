<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentOffsetService
{
    /**
     * Apply payment offset logic for a tenant when a new payment is recorded.
     * This will automatically settle the oldest pending invoices first (FIFO).
     * 
     * @param Invoice $newPayment The newly created payment record
     * @return array Summary of offset operations
     */
    public function applyPaymentOffset(Invoice $newPayment): array
    {
        // Only process if this is a received payment type
        if ($newPayment->type !== 'received' || !$newPayment->tenant_id) {
            return ['success' => false, 'message' => 'Not a tenant payment'];
        }

        // Only process if payment has been received (amount_received > 0)
        if ($newPayment->amount_received <= 0) {
            return ['success' => false, 'message' => 'No payment amount to offset'];
        }

        return DB::transaction(function () use ($newPayment) {
            $tenant = Tenant::find($newPayment->tenant_id);
            $offsetSummary = [];
            
            // Get all pending invoices for this tenant, ordered by date (oldest first)
            $pendingInvoices = Invoice::where('tenant_id', $tenant->id)
                ->where('type', 'received')
                ->where('status', 'pending')
                ->orderBy('date_received', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($pendingInvoices->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No pending invoices to offset',
                    'offsets' => []
                ];
            }

            // Calculate available payment amount (what was actually received)
            $availableAmount = floatval($newPayment->amount_received);
            
            foreach ($pendingInvoices as $invoice) {
                if ($availableAmount <= 0) {
                    break; // No more payment to distribute
                }

                $outstandingAmount = floatval($invoice->amount_total);
                
                if ($availableAmount >= $outstandingAmount) {
                    // Full payment - mark as paid
                    $invoice->status = 'paid';
                    $invoice->amount_received += $outstandingAmount;
                    $invoice->amount_total = 0; // Fully paid, no outstanding balance
                    $invoice->save();
                    
                    $availableAmount -= $outstandingAmount;
                    
                    $offsetSummary[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'amount_offset' => $outstandingAmount,
                        'status' => 'fully_paid'
                    ];
                    
                    Log::info("Payment offset: Fully paid invoice {$invoice->invoice_number} with RM " . number_format($outstandingAmount, 2));
                } else {
                    // Partial payment - mark as partial
                    $invoice->status = 'partial';
                    $invoice->amount_received += $availableAmount;
                    $invoice->amount_total = $outstandingAmount - $availableAmount; // Update remaining balance
                    $invoice->save();
                    
                    $offsetSummary[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'amount_offset' => $availableAmount,
                        'status' => 'partially_paid',
                        'remaining' => $outstandingAmount - $availableAmount
                    ];
                    
                    Log::info("Payment offset: Partially paid invoice {$invoice->invoice_number} with RM " . number_format($availableAmount, 2) . " (Remaining: RM " . number_format($outstandingAmount - $availableAmount, 2) . ")");
                    
                    $availableAmount = 0;
                }
            }

            // If there's still remaining payment, add to tenant wallet
            if ($availableAmount > 0) {
                $tenant->wallet_balance += $availableAmount;
                $tenant->save();
                
                $offsetSummary[] = [
                    'type' => 'wallet_credit',
                    'amount' => $availableAmount,
                    'message' => 'Excess payment added to wallet'
                ];
                
                Log::info("Payment offset: Added RM " . number_format($availableAmount, 2) . " to tenant {$tenant->name} wallet");
            }

            return [
                'success' => true,
                'message' => 'Payment offset completed',
                'offsets' => $offsetSummary,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name
            ];
        });
    }

    /**
     * Process all pending invoices for a specific tenant using their wallet balance.
     * 
     * @param int $tenantId
     * @return array
     */
    public function processWalletOffsets(int $tenantId): array
    {
        return DB::transaction(function () use ($tenantId) {
            $tenant = Tenant::lockForUpdate()->find($tenantId);
            
            if (!$tenant || $tenant->wallet_balance <= 0) {
                return ['success' => false, 'message' => 'No wallet balance available'];
            }

            $pendingInvoices = Invoice::where('tenant_id', $tenant->id)
                ->where('type', 'received')
                ->where('status', 'pending')
                ->orderBy('date_received', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($pendingInvoices->isEmpty()) {
                return ['success' => false, 'message' => 'No pending invoices'];
            }

            $availableWallet = floatval($tenant->wallet_balance);
            $offsetSummary = [];

            foreach ($pendingInvoices as $invoice) {
                if ($availableWallet <= 0) {
                    break;
                }

                $outstandingAmount = floatval($invoice->amount_total);

                if ($availableWallet >= $outstandingAmount) {
                    // Full payment from wallet
                    $invoice->status = 'paid';
                    $invoice->amount_received += $outstandingAmount;
                    $invoice->amount_total = 0;
                    $invoice->save();
                    
                    $availableWallet -= $outstandingAmount;
                    
                    $offsetSummary[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'amount_offset' => $outstandingAmount,
                        'status' => 'fully_paid_from_wallet'
                    ];
                } else {
                    // Partial payment from wallet
                    $invoice->status = 'partial';
                    $invoice->amount_received += $availableWallet;
                    $invoice->amount_total = $outstandingAmount - $availableWallet;
                    $invoice->save();
                    
                    $offsetSummary[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'amount_offset' => $availableWallet,
                        'status' => 'partially_paid_from_wallet',
                        'remaining' => $outstandingAmount - $availableWallet
                    ];
                    
                    $availableWallet = 0;
                }
            }

            // Update tenant wallet balance
            $tenant->wallet_balance = $availableWallet;
            $tenant->save();

            return [
                'success' => true,
                'message' => 'Wallet offset completed',
                'offsets' => $offsetSummary,
                'remaining_wallet' => $availableWallet
            ];
        });
    }

    /**
     * Get offset summary for a tenant (what would happen if we process offsets now).
     * 
     * @param int $tenantId
     * @return array
     */
    public function getOffsetPreview(int $tenantId): array
    {
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return ['success' => false, 'message' => 'Tenant not found'];
        }

        $pendingInvoices = Invoice::where('tenant_id', $tenant->id)
            ->where('type', 'received')
            ->where('status', 'pending')
            ->orderBy('date_received', 'asc')
            ->get();

        $totalOutstanding = $pendingInvoices->sum('amount_total');
        $walletBalance = floatval($tenant->wallet_balance);

        return [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'wallet_balance' => $walletBalance,
            'pending_invoices_count' => $pendingInvoices->count(),
            'total_outstanding' => $totalOutstanding,
            'can_fully_settle' => $walletBalance >= $totalOutstanding,
            'remaining_after_offset' => max(0, $walletBalance - $totalOutstanding),
            'pending_invoices' => $pendingInvoices->map(function ($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'date' => $invoice->date_received->format('Y-m-d'),
                    'amount' => floatval($invoice->amount_total),
                ];
            })
        ];
    }
}
