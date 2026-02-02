<?php

namespace App\Mail;

use App\Models\TenancyAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $record;
    public $transactions;
    public $totalCharges;
    public $totalPaid;
    public $balance;
    public $customMessage;

    public function __construct(TenancyAgreement $record, array $transactions, $totalCharges, $totalPaid, $balance, $customMessage = null)
    {
        $this->record = $record;
        $this->transactions = $transactions;
        $this->totalCharges = $totalCharges;
        $this->totalPaid = $totalPaid;
        $this->balance = $balance;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        return $this->subject('Account Statement - ' . $this->record->property->name)
            ->view('emails.account-statement');
    }
}
