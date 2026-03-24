<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class RecurringInvoiceController extends Controller
{
    public function index()
    {
        return response()->json(
            Invoice::where('is_recurring', true)->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string'],
            'customer_email' => ['nullable', 'email'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string'],
            'recurrence_period' => ['required'],
            'next_run_date' => ['nullable', 'date'],
        ]);

        return Invoice::create([
            'invoice_number' => 'TPL-' . rand(1000, 9999),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => 'PENDING',
            'is_recurring' => true,
            'recurrence_period' => $data['recurrence_period'],
            'next_run_date' => $data['next_run_date'],
        ]);
    }

    public function update(Request $request, Invoice $recurringInvoice)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string'],
            'customer_email' => ['nullable', 'email'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string'],
            'recurrence_period' => ['required'],
            'next_run_date' => ['nullable', 'date'],
            'status' => ['required', 'in:PAID,PENDING,OVERDUE'],
        ]);

        $recurringInvoice->update([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'recurrence_period' => $data['recurrence_period'],
            'next_run_date' => $data['next_run_date'],
            'status' => $data['status'],
            'is_recurring' => true,
        ]);

        return response()->json($recurringInvoice->fresh());
    }

    public function destroy(Invoice $recurringInvoice)
    {
        $recurringInvoice->delete();

        return response()->json([
            'message' => 'Recurring invoice deleted successfully.'
        ]);
    }

    public function execute(Invoice $invoice)
    {
        return Invoice::create([
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'billing_date' => now(),
            'status' => 'PENDING',
        ]);
    }
}