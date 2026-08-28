<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: add the column nullable first so we can backfill existing rows.
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable()->after('name')->constrained()->cascadeOnDelete();
        });

        // Step 2: backfill any existing fees with a fund so the column can become
        // mandatory without breaking rows created before this migration.
        // Every fee gets a same-named fund if one isn't already linked.
        $fees = DB::table('fees')->whereNull('fund_id')->get();

        foreach ($fees as $fee) {
            $fundId = DB::table('funds')->insertGetId([
                'name' => $fee->name.' Fund',
                'category' => 'fee',
                'description' => 'Auto-created fund for the "'.$fee->name.'" fee during migration.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('fees')->where('id', $fee->id)->update(['fund_id' => $fundId]);
        }

        // Step 3: now that every row has a fund_id, make it mandatory.
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fund_id');
        });
    }
};
