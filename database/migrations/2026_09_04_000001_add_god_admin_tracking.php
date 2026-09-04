<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_impersonation_tokens', function (Blueprint $table) {
            $table->string('admin_name')->nullable()->after('committee_id');
            $table->string('admin_email')->nullable()->after('admin_name');
            $table->text('reason')->nullable()->after('admin_email');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->boolean('via_god_admin')->default(false)->after('committee_name');
            $table->string('god_admin_name')->nullable()->after('via_god_admin');
        });
    }

    public function down(): void
    {
        Schema::table('admin_impersonation_tokens', function (Blueprint $table) {
            $table->dropColumn(['admin_name', 'admin_email', 'reason']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['via_god_admin', 'god_admin_name']);
        });
    }
};
