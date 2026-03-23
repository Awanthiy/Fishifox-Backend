<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'customer_id')) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('name')
                    ->constrained('customers')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('projects', 'customer_name')) {
            $projects = DB::table('projects')->select('id', 'customer_name')->get();

            foreach ($projects as $project) {
                $customer = DB::table('customers')
                    ->where('name', $project->customer_name)
                    ->first();

                if ($customer) {
                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['customer_id' => $customer->id]);
                }
            }
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('name');
            }
        });

        $projects = DB::table('projects')
            ->leftJoin('customers', 'projects.customer_id', '=', 'customers.id')
            ->select('projects.id', 'customers.name as customer_name')
            ->get();

        foreach ($projects as $project) {
            DB::table('projects')
                ->where('id', $project->id)
                ->update(['customer_name' => $project->customer_name]);
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }
        });
    }
};