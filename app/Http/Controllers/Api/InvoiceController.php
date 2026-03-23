<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $q = Invoice::query()->orderByDesc('id');

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $q->get()->map(fn (Invoice $inv) => $this->formatInvoice($inv))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'billing_date' => ['nullable', 'date'],
            'status' => ['required', 'in:PAID,PENDING,OVERDUE'],
        ]);

        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $invoice = Invoice::create($data);

        return response()->json($this->formatInvoice($invoice), 201);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number,' . $invoice->id],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'billing_date' => ['nullable', 'date'],
            'status' => ['required', 'in:PAID,PENDING,OVERDUE'],
        ]);

        $invoice->update($data);

        return response()->json($this->formatInvoice($invoice->fresh()));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
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
            return response()->json([
                'message' => 'Customer email not found'
            ], 400);
        }

        try {
            Mail::to($invoice->customer_email)->send(new InvoiceMail($invoice));

            $invoice->email_sent_at = now();
            $invoice->save();

            return response()->json([
                'message' => 'Email sent successfully',
                'sent_to' => $invoice->customer_email,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Invoice mail failed', [
                'invoice_id' => $invoice->id,
                'email' => $invoice->customer_email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Email failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatInvoice(Invoice $inv): array
    {
        return [
            'id' => (string) $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_name' => $inv->customer_name,
            'customer_email' => $inv->customer_email,
            'amount' => (float) $inv->amount,
            'currency' => $inv->currency,
            'date' => optional($inv->billing_date)->format('Y-m-d'),
            'status' => $inv->status,
        ];
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $countThisYear = Invoice::where('invoice_number', 'like', "INV-{$year}-%")->count() + 1;
        $seq = str_pad((string) $countThisYear, 3, '0', STR_PAD_LEFT);

        return "INV-{$year}-{$seq}";
    }
}