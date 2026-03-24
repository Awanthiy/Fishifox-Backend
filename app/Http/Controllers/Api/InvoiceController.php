<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    // GET /api/invoices?search=
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
            $q->get()->map(fn(Invoice $inv) => $this->transformInvoice($inv))
        );
    }

    // POST /api/invoices
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

        if (empty($data['customer_email'])) {
            $customer = Customer::where('name', $data['customer_name'])->first();
            if ($customer && !empty($customer->email)) {
                $data['customer_email'] = $customer->email;
            }
        }

        $inv = Invoice::create($data);

        return response()->json($this->transformInvoice($inv), 201);
    }

    // PUT /api/invoices/{invoice}
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

        if (empty($data['customer_email'])) {
            $customer = Customer::where('name', $data['customer_name'])->first();
            if ($customer && !empty($customer->email)) {
                $data['customer_email'] = $customer->email;
            }
        }

        $invoice->update($data);

        return response()->json($this->transformInvoice($invoice->fresh()));
    }

    // DELETE /api/invoices/{invoice}
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // GET /api/invoices/{invoice}/download
    public function download(Invoice $invoice)
    {
        $company = $this->getCompanySettingsForPdf();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
        ]);

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    // POST /api/invoices/{invoice}/send-email
    public function sendEmail(Invoice $invoice)
    {
        $email = $invoice->customer_email;

        if (empty($email)) {
            $customer = Customer::where('name', $invoice->customer_name)->first();
            if ($customer && !empty($customer->email)) {
                $email = $customer->email;
            }
        }

        if (empty($email)) {
            return response()->json([
                'message' => 'Customer email not found for this invoice.'
            ], 422);
        }

        Mail::to($email)->send(new InvoiceMail($invoice));

        $invoice->update([
            'customer_email' => $email,
            'email_sent_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invoice email sent successfully.'
        ]);
    }

    private function getCompanySettingsForPdf(): array
    {
        $company = Setting::where('key', 'company')->first()?->value ?? [];

        $company = array_merge([
            'company_name' => 'FishiFox',
            'company_email' => '',
            'company_phone' => '',
            'company_address' => '',
            'invoice_header' => '',
            'invoice_footer' => '',
            'company_logo' => null,
            'company_logo_path' => null,
        ], $company);

        if (!empty($company['company_logo'])) {
            $logoName = basename($company['company_logo']);
            $fullPath = public_path('uploads/company/' . $logoName);

            if (file_exists($fullPath)) {
                $company['company_logo_path'] = $fullPath;
            }
        }

        return $company;
    }

    private function transformInvoice(Invoice $inv): array
    {
        return [
            'id' => (int) $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_name' => $inv->customer_name,
            'customer_email' => $inv->customer_email,
            'amount' => (float) $inv->amount,
            'currency' => $inv->currency,
            'date' => optional($inv->billing_date)->format('Y-m-d'),
            'status' => $inv->status,
            'email_sent_at' => optional($inv->email_sent_at)?->toDateTimeString(),
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