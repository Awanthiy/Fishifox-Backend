<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_type')) {
                $table->string('customer_type')->default('Individual')->after('phone');
            }

            if (!Schema::hasColumn('customers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('customer_type');
            }

            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable()->after('contact_person');
            }

            if (!Schema::hasColumn('customers', 'total_billed')) {
                $table->decimal('total_billed', 12, 2)->default(0)->after('active_projects');
            }
        });

        if (Schema::hasColumn('customers', 'status')) {
            DB::table('customers')->where('status', 'Enterprise')->update(['status' => 'Active']);
            DB::table('customers')->where('status', 'Premium')->update(['status' => 'Active']);
            DB::table('customers')->where('status', 'Regular')->update(['status' => 'Active']);
            DB::table('customers')->where('status', 'New')->update(['status' => 'Lead']);
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'customer_type')) {
                $table->dropColumn('customer_type');
            }

            if (Schema::hasColumn('customers', 'contact_person')) {
                $table->dropColumn('contact_person');
            }

            if (Schema::hasColumn('customers', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('customers', 'total_billed')) {
                $table->dropColumn('total_billed');
            }
        });
    }
};