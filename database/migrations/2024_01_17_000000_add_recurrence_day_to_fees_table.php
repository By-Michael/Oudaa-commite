<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            // Day of the month (1-31) this fee is expected to recur on —
            // e.g. 5 means "due on the 5th" for monthly, or "the 5th of
            // the first month of the quarter/year" for quarterly/yearly.
            // Optional: if left blank when the fee is created, it falls
            // back to the day-of-month the fee itself was created on.
            $table->unsignedTinyInteger('recurrence_day')->nullable()->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('recurrence_day');
        });
    }
};
