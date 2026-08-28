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
            $table->renameColumn('budget', 'planned_budget');
            $table->date('start_date')->nullable()->after('fund_id');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // Backfill any project without a fund into a dedicated "Unassigned Projects"
        // fund, so fund_id can be made mandatory without losing existing data.
        $orphanCount = DB::table('projects')->whereNull('fund_id')->count();

        if ($orphanCount > 0) {
            $fundId = DB::table('funds')->insertGetId([
                'name' => 'Unassigned Projects Fund',
                'category' => 'general',
                'description' => 'Auto-created during migration to hold projects that had no fund linked.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('projects')->whereNull('fund_id')->update(['fund_id' => $fundId]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable()->change();
            $table->dropColumn(['start_date', 'end_date']);
            $table->renameColumn('planned_budget', 'budget');
        });
    }
};
