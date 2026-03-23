<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $q = Customer::query()->orderByDesc('id');

        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_type', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $q->get()->map(function (Customer $c) {
                return [
                    'id' => (string) $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'customerType' => $c->customer_type,
                    'contactPerson' => $c->contact_person,
                    'address' => $c->address,
                    'activeProjects' => (int) ($c->active_projects ?? 0),
                    'totalBilled' => (float) ($c->total_billed ?? 0),
                    'status' => $c->status,
                    'createdAt' => optional($c->created_at)?->toISOString(),
                    'updatedAt' => optional($c->updated_at)?->toISOString(),
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customerType' => ['required', Rule::in(['Individual', 'Company'])],
            'contactPerson' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Lead'])],
        ]);

        $c = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'customer_type' => $data['customerType'],
            'contact_person' => $data['contactPerson'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        return response()->json([
            'id' => (string) $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'customerType' => $c->customer_type,
            'contactPerson' => $c->contact_person,
            'address' => $c->address,
            'activeProjects' => (int) ($c->active_projects ?? 0),
            'totalBilled' => (float) ($c->total_billed ?? 0),
            'status' => $c->status,
            'createdAt' => optional($c->created_at)?->toISOString(),
            'updatedAt' => optional($c->updated_at)?->toISOString(),
        ], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customerType' => ['required', Rule::in(['Individual', 'Company'])],
            'contactPerson' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Lead'])],
        ]);

        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'customer_type' => $data['customerType'],
            'contact_person' => $data['contactPerson'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        $customer->refresh();

        return response()->json([
            'id' => (string) $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'customerType' => $customer->customer_type,
            'contactPerson' => $customer->contact_person,
            'address' => $customer->address,
            'activeProjects' => (int) ($customer->active_projects ?? 0),
            'totalBilled' => (float) ($customer->total_billed ?? 0),
            'status' => $customer->status,
            'createdAt' => optional($customer->created_at)?->toISOString(),
            'updatedAt' => optional($customer->updated_at)?->toISOString(),
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}