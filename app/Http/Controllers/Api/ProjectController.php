<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 12);
        $perPage = $perPage > 0 ? min($perPage, 50) : 12;

        $q = Project::query()
            ->with('customer')
            ->latest();

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $projects = $q->paginate($perPage);

        $projects->getCollection()->transform(function (Project $project) {
            return [
                'id' => (string) $project->id,
                'name' => $project->name,
                'customerId' => $project->customer_id ? (string) $project->customer_id : null,
                'customerName' => $project->customer?->name,
                'status' => $project->status,
                'progress' => (int) ($project->progress ?? 0),
                'createdAt' => optional($project->created_at)?->toISOString(),
                'updatedAt' => optional($project->updated_at)?->toISOString(),
            ];
        });

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'status' => ['required', Rule::in(['Pending', 'Ongoing', 'Completed', 'Cancelled'])],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $project = Project::create([
            'name' => $data['name'],
            'customer_id' => $data['customerId'],
            'status' => $data['status'],
            'progress' => $data['progress'] ?? 0,
        ]);

        $project->load('customer');

        return response()->json([
            'id' => (string) $project->id,
            'name' => $project->name,
            'customerId' => (string) $project->customer_id,
            'customerName' => $project->customer?->name,
            'status' => $project->status,
            'progress' => (int) $project->progress,
            'createdAt' => optional($project->created_at)?->toISOString(),
            'updatedAt' => optional($project->updated_at)?->toISOString(),
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'status' => ['required', Rule::in(['Pending', 'Ongoing', 'Completed', 'Cancelled'])],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $project->update([
            'name' => $data['name'],
            'customer_id' => $data['customerId'],
            'status' => $data['status'],
            'progress' => $data['progress'] ?? 0,
        ]);

        $project->load('customer');

        return response()->json([
            'id' => (string) $project->id,
            'name' => $project->name,
            'customerId' => (string) $project->customer_id,
            'customerName' => $project->customer?->name,
            'status' => $project->status,
            'progress' => (int) $project->progress,
            'createdAt' => optional($project->created_at)?->toISOString(),
            'updatedAt' => optional($project->updated_at)?->toISOString(),
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}