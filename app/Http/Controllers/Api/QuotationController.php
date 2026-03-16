<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $q = Quotation::with('services')->orderByDesc('id');

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $q->get()->map(fn (Quotation $x) => [
                'id' => (string) $x->id,
                'number' => $x->quote_number,
                'customer_id' => $x->customer_id ? (string) $x->customer_id : null,
                'customer' => $x->customer,
                'customer_email' => $x->customer_email,
                'customer_phone' => $x->customer_phone,
                'customer_address' => $x->customer_address,
                'amount' => (float) $x->amount,
                'currency' => $x->currency,
                'status' => $x->status,
                'date' => optional($x->quote_date)->format('Y-m-d'),
                'converted' => (bool) $x->converted,
                'services' => $x->services->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'service_id' => $s->service_id ? (string) $s->service_id : null,
                    'service_name' => $s->service_name,
                    'description' => $s->description,
                    'qty' => (int) $s->qty,
                    'unit_price' => (float) $s->unit_price,
                    'total' => (float) $s->total,
                ])->values(),
            ])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quote_number' => ['nullable', 'string', 'max:50'],
            'customer_id' => ['nullable', 'integer'],
            'customer' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'quote_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Pending,Approved,Rejected'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['nullable', 'integer'],
            'services.*.service_name' => ['required', 'string', 'max:255'],
            'services.*.description' => ['nullable', 'string'],
            'services.*.qty' => ['required', 'numeric', 'min:1'],
            'services.*.unit_price' => ['required', 'numeric', 'min:0'],
            'services.*.total' => ['required', 'numeric', 'min:0'],
        ]);

        if (empty($data['quote_number'])) {
            $data['quote_number'] = $this->generateQuoteNumber();
        }

        return DB::transaction(function () use ($data) {
            $services = $data['services'] ?? [];
            unset($data['services']);

            $q = Quotation::create($data);

            foreach ($services as $service) {
                $q->services()->create([
                    'service_id' => $service['service_id'] ?? null,
                    'service_name' => $service['service_name'],
                    'description' => $service['description'] ?? null,
                    'qty' => $service['qty'],
                    'unit_price' => $service['unit_price'],
                    'total' => $service['total'],
                ]);
            }

            $q->load('services');

            return response()->json([
                'id' => (string) $q->id,
                'number' => $q->quote_number,
                'customer_id' => $q->customer_id ? (string) $q->customer_id : null,
                'customer' => $q->customer,
                'customer_email' => $q->customer_email,
                'customer_phone' => $q->customer_phone,
                'customer_address' => $q->customer_address,
                'amount' => (float) $q->amount,
                'currency' => $q->currency,
                'status' => $q->status,
                'date' => optional($q->quote_date)->format('Y-m-d'),
                'converted' => (bool) $q->converted,
                'services' => $q->services->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'service_id' => $s->service_id ? (string) $s->service_id : null,
                    'service_name' => $s->service_name,
                    'description' => $s->description,
                    'qty' => (int) $s->qty,
                    'unit_price' => (float) $s->unit_price,
                    'total' => (float) $s->total,
                ])->values(),
            ], 201);
        });
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'quote_number' => ['required', 'string', 'max:50', 'unique:quotations,quote_number,' . $quotation->id],
            'customer_id' => ['nullable', 'integer'],
            'customer' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'quote_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Pending,Approved,Rejected'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['nullable', 'integer'],
            'services.*.service_name' => ['required', 'string', 'max:255'],
            'services.*.description' => ['nullable', 'string'],
            'services.*.qty' => ['required', 'numeric', 'min:1'],
            'services.*.unit_price' => ['required', 'numeric', 'min:0'],
            'services.*.total' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data, $quotation) {
            $services = $data['services'] ?? [];
            unset($data['services']);

            $quotation->update($data);

            $quotation->services()->delete();

            foreach ($services as $service) {
                $quotation->services()->create([
                    'service_id' => $service['service_id'] ?? null,
                    'service_name' => $service['service_name'],
                    'description' => $service['description'] ?? null,
                    'qty' => $service['qty'],
                    'unit_price' => $service['unit_price'],
                    'total' => $service['total'],
                ]);
            }

            $quotation->load('services');

            return response()->json([
                'id' => (string) $quotation->id,
                'number' => $quotation->quote_number,
                'customer_id' => $quotation->customer_id ? (string) $quotation->customer_id : null,
                'customer' => $quotation->customer,
                'customer_email' => $quotation->customer_email,
                'customer_phone' => $quotation->customer_phone,
                'customer_address' => $quotation->customer_address,
                'amount' => (float) $quotation->amount,
                'currency' => $quotation->currency,
                'status' => $quotation->status,
                'date' => optional($quotation->quote_date)->format('Y-m-d'),
                'converted' => (bool) $quotation->converted,
                'services' => $quotation->services->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'service_id' => $s->service_id ? (string) $s->service_id : null,
                    'service_name' => $s->service_name,
                    'description' => $s->description,
                    'qty' => (int) $s->qty,
                    'unit_price' => (float) $s->unit_price,
                    'total' => (float) $s->total,
                ])->values(),
            ]);
        });
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function convert(Quotation $quotation)
    {
        if ($quotation->status !== 'Approved') {
            return response()->json(['message' => 'Only Approved quotations can be converted'], 422);
        }

        $quotation->update([
            'converted' => true,
            'converted_at' => now(),
        ]);

        return response()->json(['message' => 'Converted']);
    }

    private function generateQuoteNumber(): string
    {
        $next = (int) (Quotation::max('id') ?? 0) + 8801;
        return 'QT-' . $next;
    }
}