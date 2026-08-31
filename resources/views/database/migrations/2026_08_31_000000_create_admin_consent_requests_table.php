<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pending "may the God Admin dashboard act as this user?" prompts.
     * Nothing in app/Http/Controllers/Admin ever logs a user in, or
     * hands out a working /admin-bridge link, until a row here is
     * status=approved by the committee member themselves.
     */
    public function up(): void
    {
        Schema::create('admin_consent_requests', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('committee_id');
            $table->string('tenant_slug');
            $table->text('reason')->nullable();
            $table->string('callback_url', 2048); // where we POST the signed decision back
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['committee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_consent_requests');
    }
};
