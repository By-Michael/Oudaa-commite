<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Populated by a DB::listen() hook in AppServiceProvider — see
        // there for why the threshold is what it is and why inserts into
        // this table itself never re-trigger the listener.
        Schema::create('slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('sql');
            $table->json('bindings')->nullable();
            $table->unsignedInteger('time_ms');
            $table->string('path', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('time_ms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slow_queries');
    }
};
