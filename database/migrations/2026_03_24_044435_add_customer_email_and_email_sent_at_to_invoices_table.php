<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            // ✅ add only if NOT exists
            if (!Schema::hasColumn('invoices', 'customer_email')) {
                $table->string('customer_email')
                      ->nullable()
                      ->after('customer_name');
            }

            if (!Schema::hasColumn('invoices', 'email_sent_at')) {
                $table->timestamp('email_sent_at')
                      ->nullable()
                      ->after('status');
            }

        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            // ✅ drop only if exists
            if (Schema::hasColumn('invoices', 'customer_email')) {
                $table->dropColumn('customer_email');
            }

            if (Schema::hasColumn('invoices', 'email_sent_at')) {
                $table->dropColumn('email_sent_at');
            }

        });
    }
};