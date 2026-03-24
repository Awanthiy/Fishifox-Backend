<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->loadMissing('customer');

        $companyRow = Setting::query()->where('key', 'company')->first();
        $company = $companyRow?->value ?? [
            'company_name' => 'My Company',
            'company_email' => '',
            'company_phone' => '',
            'company_address' => '',
            'company_logo' => null,
        ];

        $company['company_logo_path'] = null;

        if (!empty($company['company_logo'])) {
            $logoName = basename($company['company_logo']);
            $fullPath = public_path('uploads/company/' . $logoName);

            if (file_exists($fullPath)) {
                $company['company_logo_path'] = $fullPath;
            }
        }

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
        ]);

        return $pdf->download('invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf');
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $invoice->loadMissing('customer');

        $companyRow = Setting::query()->where('key', 'company')->first();
        $company = $companyRow?->value ?? [
            'company_name' => 'My Company',
            'company_email' => '',
            'company_phone' => '',
            'company_address' => '',
            'company_logo' => null,
        ];

        $company['company_logo_path'] = null;

        if (!empty($company['company_logo'])) {
            $logoName = basename($company['company_logo']);
            $fullPath = public_path('uploads/company/' . $logoName);

            if (file_exists($fullPath)) {
                $company['company_logo_path'] = $fullPath;
            }
        }

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
        ]);

        $pdfContent = $pdf->output();

        $to = $data['email'];
        $subject = $data['subject'] ?? ('Invoice #' . ($invoice->invoice_number ?? $invoice->id));
        $messageText = $data['message'] ?? 'Please find your invoice attached.';

        Mail::send('emails.invoice', ['invoice' => $invoice, 'messageText' => $messageText], function ($message) use ($to, $subject, $pdfContent, $invoice) {
            $message->to($to)
                ->subject($subject)
                ->attachData(
                    $pdfContent,
                    'invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf',
                    ['mime' => 'application/pdf']
                );
        });

        return response()->json([
            'message' => 'Invoice email sent successfully.',
        ]);
    }
}