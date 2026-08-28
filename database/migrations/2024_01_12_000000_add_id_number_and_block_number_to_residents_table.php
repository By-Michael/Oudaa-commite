<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Required identity field (national ID / passport / resident card number).
            // Nullable at the DB level to avoid breaking existing rows on deploy,
            // but enforced as required in the request validation layer for new records.
            $table->string('id_number')->nullable()->unique()->after('name');

            // Optional — not every property is organized into blocks.
            $table->string('block_number')->nullable()->after('unit_number');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropUnique(['id_number']);
            $table->dropColumn(['id_number', 'block_number']);
        });
    }
};
