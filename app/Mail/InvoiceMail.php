<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;

class InvoiceMail extends Mailable
{
    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $this->invoice
        ]);

        return $this->subject('Invoice ' . $this->invoice->invoice_number)
            ->view('emails.invoice')
            ->attachData($pdf->output(), $this->invoice->invoice_number . '.pdf');
    }
}