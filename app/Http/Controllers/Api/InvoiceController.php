<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $q = Invoice::query()->orderByDesc('id');

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('invoice_number', 'like', "%{$search}%")
                   ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $q->get()->map(fn (Invoice $inv) => [
                'id' => (string) $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer_name,
                'customer_email' => $inv->customer_email, // ✅
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
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'], // ✅
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'billing_date' => ['nullable', 'date'],
            'status' => ['required', 'in:PAID,PENDING,OVERDUE'],
        ]);

        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $inv = Invoice::create($data);

        return response()->json([
            'id' => (string) $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_name' => $inv->customer_name,
            'customer_email' => $inv->customer_email, // ✅
            'amount' => (float) $inv->amount,
            'currency' => $inv->currency,
            'date' => optional($inv->billing_date)->format('Y-m-d'),
            'status' => $inv->status,
        ], 201);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number,' . $invoice->id],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'], // ✅
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'billing_date' => ['nullable', 'date'],
            'status' => ['required', 'in:PAID,PENDING,OVERDUE'],
        ]);

        $invoice->update($data);

        return response()->json([
            'id' => (string) $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email, // ✅
            'amount' => (float) $invoice->amount,
            'currency' => $invoice->currency,
            'date' => optional($invoice->billing_date)->format('Y-m-d'),
            'status' => $invoice->status,
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function download(Invoice $invoice)
    {
        $content = "Invoice: {$invoice->invoice_number}\n"
            . "Customer: {$invoice->customer_name}\n"
            . "Email: {$invoice->customer_email}\n" // ✅
            . "Amount: {$invoice->currency} {$invoice->amount}\n"
            . "Billing Date: " . optional($invoice->billing_date)->format('Y-m-d') . "\n"
            . "Status: {$invoice->status}\n";

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.txt"',
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $countThisYear = Invoice::where('invoice_number', 'like', "INV-{$year}-%")->count() + 1;
        $seq = str_pad((string) $countThisYear, 3, '0', STR_PAD_LEFT);

        return "INV-{$year}-{$seq}";
    }
}