<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('duration_ms');
            $table->unsignedSmallInteger('status_code');
            $table->string('method', 8);
            $table->string('path', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });

        // One-time impersonation tokens issued to the admin app, redeemed
        // exactly once at /admin-bridge/{token}.
        Schema::create('admin_impersonation_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('committee_id');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
        Schema::dropIfExists('admin_impersonation_tokens');
    }
};
