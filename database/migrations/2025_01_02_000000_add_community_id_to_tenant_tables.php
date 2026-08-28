<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every table below used to be a table in its own tenant's private
     * SQLite file — isolation was "which file are we connected to".
     * Now every community shares one database, so isolation moves to
     * this column instead (enforced automatically for every query via
     * App\Models\Concerns\BelongsToCommunity — see that trait).
     *
     * Two uniques that used to be safe as globally-unique (because
     * each tenant had its own file) would now incorrectly stop two
     * *different* communities from both having, say, a resident with
     * id_number "AB123": committees.email and residents/employees
     * .id_number. Those become composite (community_id, column).
     */
    public function up(): void
    {
        $tables = [
            'committees', 'residents', 'fees', 'funds', 'payments',
            'expenses', 'projects', 'employees', 'audit_logs', 'tenant_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('community_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('committees', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['community_id', 'email']);
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropUnique(['id_number']);
            $table->unique(['community_id', 'id_number']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['id_number']);
            $table->unique(['community_id', 'id_number']);
        });
    }

    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropUnique(['community_id', 'email']);
            $table->unique('email');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropUnique(['community_id', 'id_number']);
            $table->unique('id_number');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['community_id', 'id_number']);
            $table->unique('id_number');
        });

        $tables = [
            'committees', 'residents', 'fees', 'funds', 'payments',
            'expenses', 'projects', 'employees', 'audit_logs', 'tenant_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('community_id');
            });
        }
    }
};
