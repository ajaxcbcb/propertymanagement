<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'received' => 'Received',
                        'expenditure' => 'Expenses',
                    ])
                    ->required()
                    ->default('received')
                    ->live(),
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(fn ($get) => $get('type') === 'received')
                    ->visible(true)
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('tenancy_agreement_id')
                    ->label('Tenancy Agreement')
                    ->relationship('tenancyAgreement', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tenant->name} - {$record->property->name}")
                    ->options(function (callable $get) {
                        $propertyId = $get('property_id');
                        if (!$propertyId) {
                            // If no property selected yet, maybe show all or none. 
                            // Existing logic relies on search.
                            // But for mismatched constraint, we want to limit options if propery is selected.
                            return \App\Models\TenancyAgreement::with(['tenant', 'property'])
                                ->get()
                                ->mapWithKeys(fn ($item) => [$item->id => "{$item->tenant->name} - {$item->property->name}"]);
                        }
                        
                        // Filter by selected property
                        return \App\Models\TenancyAgreement::where('property_id', $propertyId)
                            ->with('tenant')
                            ->get()
                            ->mapWithKeys(fn ($item) => [$item->id => "{$item->tenant->name} - " . ($item->is_active ? 'Active' : 'Ended')]);
                    })
                    ->searchable() // search is handled by options keys/values usually, or we can keep relationship?
                    // If we use options(), we lose searchable() relationship power if not fully loaded.
                    // But avoiding mismatch is priority.
                    ->preload()
                    ->required(fn ($get) => $get('type') === 'received')
                    ->visible(true) // Always visible to allow linking expense to tenancy
                    ->live()
                    ->validationMessages([
                        'in' => 'The selected agreement does not belong to the chosen property.',
                    ])
                    ->rules([
                        function (\Filament\Forms\Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $propertyId = $get('property_id');
                                if ($propertyId && $value) {
                                    $agreement = \App\Models\TenancyAgreement::find($value);
                                    if ($agreement && $agreement->property_id != $propertyId) {
                                        $fail('The selected agreement does not belong to the chosen property.');
                                    }
                                }
                            };
                        },
                    ])
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (!$state) return;
                        
                        $agreement = \App\Models\TenancyAgreement::with('tenant')->find($state);
                        
                        if ($agreement) {
                             $set('tenant_id', $agreement->tenant_id);
                             
                             if (!$get('property_id') && $agreement->property_id) {
                                 $set('property_id', $agreement->property_id);
                             }
                        }

                        // Logic for received type
                        if ($get('type') === 'received' && $agreement) {
                            $baseAmount = $agreement->agreed_rent;
                            $set('amount_received', $baseAmount);
                            
                            $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                            $sstAmount = 0;
                            if ($agreement->tenant && $agreement->tenant->is_sst_registered) {
                                $sstAmount = $baseAmount * ($sstRate / 100);
                            }
                            $set('amount_sst', $sstAmount);
                            $set('amount_total', $baseAmount + $sstAmount);

                            static::updateStatus($set, $get);
                        }
                    }),
                Select::make('property_id')
                    ->label('Property')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn ($get) => $get('type') === 'expenditure')
                    ->visible(true) // Always visible to ensure context
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                         // Auto-select active agreement for this property
                         if ($state) {
                             $activeAgreement = \App\Models\TenancyAgreement::where('property_id', $state)
                                 ->where('is_active', true)
                                 ->first();
                             
                             if ($activeAgreement) {
                                 $set('tenancy_agreement_id', $activeAgreement->id);
                                 // Auto-set Tenant too for easier UX
                                 $set('tenant_id', $activeAgreement->tenant_id);
                             } else {
                                 $set('tenancy_agreement_id', null);
                                 // Don't clear tenant_id necessarily, or maybe yes?
                             }
                         }
                    }),
                TextInput::make('invoice_number')
                    ->label(fn ($get) => $get('type') === 'expenditure' ? 'Reference No. / Receipt No.' : 'Bank-in Receipt / Receipt No.')
                    ->placeholder('Enter bank slip or receipt reference number')
                    ->required()
                    ->rules([
                        function (\Filament\Forms\Get $get, $record) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                // Only enforce uniqueness for "received" type (Payments)
                                if ($get('type') !== 'received') {
                                    return;
                                }
                                
                                $query = \App\Models\Invoice::where('invoice_number', $value)
                                    ->where('type', 'received');
                                
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                
                                if ($query->exists()) {
                                    $fail('The receipt number has already been taken.');
                                }
                            };
                        },
                    ])
                    ->maxLength(255),
                DatePicker::make('date_received')
                    ->label(fn ($get) => $get('type') === 'expenditure' ? 'Date Paid' : 'Date Received')
                    ->required()
                    ->default(now())
                    ->live(),
                TextInput::make('amount_received')
                    ->label(fn ($get) => $get('type') === 'expenditure' ? 'Amount Spent' : 'Amount Received')
                    ->required()
                    ->numeric()
                    ->prefix('RM')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($get('type') === 'expenditure') {
                            $set('amount_sst', 0);
                            $set('amount_total', floatval($state));
                            return;
                        }

                        $agreementId = $get('tenancy_agreement_id');
                        if (!$agreementId) return;
                        
                        $agreement = \App\Models\TenancyAgreement::with('tenant')->find($agreementId);
                        $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                        $sstAmount = 0;
                        
                        if ($agreement && $agreement->tenant && $agreement->tenant->is_sst_registered) {
                            $sstAmount = floatval($state) * ($sstRate / 100);
                        }
                        
                        $set('amount_sst', $sstAmount);
                        $set('amount_total', floatval($state) + $sstAmount);
                        static::updateStatus($set, $get);
                    }),
                TextInput::make('amount_sst')
                    ->label('SST Amount')
                    ->required()
                    ->numeric()
                    ->prefix('RM')
                    ->default(0)
                    ->readOnly()
                    ->visible(fn ($get) => $get('type') === 'received')
                    ->live(),
                TextInput::make('amount_total')
                    ->label(fn ($get) => $get('type') === 'expenditure' ? 'Total Spent' : 'Total (Net)')
                    ->required()
                    ->numeric()
                    ->prefix('RM')
                    ->readOnly(),
                TextInput::make('description')
                    ->label('Description / Remarks')
                    ->placeholder('e.g. Repair work, Utility bill, etc.')
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('type') === 'expenditure'),
                Select::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'void' => 'Void',
                    ])
                    ->required()
                    ->default('paid'),
            ]);
    }

    protected static function updateStatus(callable $set, callable $get)
    {
        $received = floatval($get('amount_received') ?? 0);
        
        if ($received > 0) {
            $set('status', 'paid');
        } else {
            $set('status', 'void');
        }
    }
}
