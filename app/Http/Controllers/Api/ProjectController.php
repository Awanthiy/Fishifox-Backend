<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $perPage = (int) $request->query('per_page', 12);
            $perPage = $perPage > 0 ? min($perPage, 50) : 12;

            $hasCustomerId = Schema::hasColumn('projects', 'customer_id');
            $hasCustomerName = Schema::hasColumn('projects', 'customer_name');

            $q = Project::query()->latest();

            if ($hasCustomerId) {
                $q->with('customer');
            }

            if ($search !== '') {
                $q->where(function ($qq) use ($search, $hasCustomerId, $hasCustomerName) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");

                    if ($hasCustomerName) {
                        $qq->orWhere('customer_name', 'like', "%{$search}%");
                    }

                    if ($hasCustomerId) {
                        $qq->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                    }
                });
            }

            $projects = $q->paginate($perPage);

            $projects->getCollection()->transform(function (Project $project) use ($hasCustomerId, $hasCustomerName) {
                $customerName = null;

                if ($hasCustomerId) {
                    $customerName = $project->customer?->name;
                }

                if (!$customerName && $hasCustomerName) {
                    $customerName = $project->customer_name;
                }

                return [
                    'id' => (string) $project->id,
                    'name' => $project->name,
                    'customerId' => $hasCustomerId ? ($project->customer_id ? (string) $project->customer_id : null) : null,
                    'customerName' => $customerName,
                    'status' => $project->status,
                    'progress' => (int) ($project->progress ?? 0),
                    'createdAt' => optional($project->created_at)?->toISOString(),
                    'updatedAt' => optional($project->updated_at)?->toISOString(),
                ];
            });

            return response()->json($projects);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $hasCustomerId = Schema::hasColumn('projects', 'customer_id');
            $hasCustomerName = Schema::hasColumn('projects', 'customer_name');

            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', Rule::in(['Pending', 'Ongoing', 'Completed', 'Cancelled', 'In Progress'])],
                'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            ];

            if ($hasCustomerId) {
                $rules['customerId'] = ['required', 'integer', 'exists:customers,id'];
            } else {
                $rules['customerName'] = ['required', 'string', 'max:255'];
            }

            $data = $request->validate($rules);

            $payload = [
                'name' => $data['name'],
                'status' => $data['status'] === 'In Progress' ? 'Ongoing' : $data['status'],
                'progress' => $data['progress'] ?? 0,
            ];

            if ($hasCustomerId) {
                $payload['customer_id'] = $data['customerId'];

                if ($hasCustomerName) {
                    $customer = Customer::find($data['customerId']);
                    $payload['customer_name'] = $customer?->name;
                }
            } else {
                $payload['customer_name'] = $data['customerName'] ?? null;
            }

            $project = Project::create($payload);

            if ($hasCustomerId) {
                $project->load('customer');
            }

            return response()->json([
                'id' => (string) $project->id,
                'name' => $project->name,
                'customerId' => $hasCustomerId ? ($project->customer_id ? (string) $project->customer_id : null) : null,
                'customerName' => $hasCustomerId
                    ? ($project->customer?->name ?? ($hasCustomerName ? $project->customer_name : null))
                    : $project->customer_name,
                'status' => $project->status,
                'progress' => (int) ($project->progress ?? 0),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            $hasCustomerId = Schema::hasColumn('projects', 'customer_id');
            $hasCustomerName = Schema::hasColumn('projects', 'customer_name');

            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', Rule::in(['Pending', 'Ongoing', 'Completed', 'Cancelled', 'In Progress'])],
                'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            ];

            if ($hasCustomerId) {
                $rules['customerId'] = ['required', 'integer', 'exists:customers,id'];
            } else {
                $rules['customerName'] = ['required', 'string', 'max:255'];
            }

            $data = $request->validate($rules);

            $payload = [
                'name' => $data['name'],
                'status' => $data['status'] === 'In Progress' ? 'Ongoing' : $data['status'],
                'progress' => $data['progress'] ?? 0,
            ];

            if ($hasCustomerId) {
                $payload['customer_id'] = $data['customerId'];

                if ($hasCustomerName) {
                    $customer = Customer::find($data['customerId']);
                    $payload['customer_name'] = $customer?->name;
                }
            } else {
                $payload['customer_name'] = $data['customerName'] ?? null;
            }

            $project->update($payload);

            if ($hasCustomerId) {
                $project->load('customer');
            }

            return response()->json([
                'id' => (string) $project->id,
                'name' => $project->name,
                'customerId' => $hasCustomerId ? ($project->customer_id ? (string) $project->customer_id : null) : null,
                'customerName' => $hasCustomerId
                    ? ($project->customer?->name ?? ($hasCustomerName ? $project->customer_name : null))
                    : $project->customer_name,
                'status' => $project->status,
                'progress' => (int) ($project->progress ?? 0),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function destroy(Project $project)
    {
        try {
            $project->delete();

            return response()->json([
                'message' => 'Deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}