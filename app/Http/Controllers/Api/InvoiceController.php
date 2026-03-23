<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Invoice::orderByDesc('id')->get()->map(fn ($inv) => [
                'id' => (string) $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer_name,
                'customer_email' => $inv->customer_email,
                'amount' => (float) $inv->amount,
                'currency' => $inv->currency,
                'date' => optional($inv->billing_date)->format('Y-m-d'),
                'status' => $inv->status,
            ])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required'],
            'customer_email' => ['nullable', 'email'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required'],
            'billing_date' => ['nullable', 'date'],
            'status' => ['required'],
        ]);

        $data['invoice_number'] = 'INV-' . rand(1000, 9999);

        return Invoice::create($data);
    }

    public function download(Invoice $invoice)
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function sendEmail(Invoice $invoice)
    {
        if (!$invoice->customer_email) {
            return response()->json(['message' => 'No email'], 400);
        }

        Mail::to($invoice->customer_email)->send(new InvoiceMail($invoice));

        $invoice->email_sent_at = now();
        $invoice->save();

        return response()->json(['message' => 'Email sent']);
    }
}