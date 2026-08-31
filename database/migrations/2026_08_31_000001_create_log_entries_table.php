<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backs the admin dashboard's live "Logs" panel. Written to by the
        // 'database' log channel (see App\Logging\DatabaseLogHandler) so the
        // panel works regardless of the container's disk being ephemeral or
        // there being more than one instance behind the load balancer —
        // both of which broke the old storage/logs/laravel.log file-tail.
        Schema::create('log_entries', function (Blueprint $table) {
            $table->id();
            $table->string('level', 16)->index();
            $table->string('message', 1000);
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_entries');
    }
};
