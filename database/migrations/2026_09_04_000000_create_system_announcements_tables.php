<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Announcements pushed from the God Admin dashboard (see
     * AgentApiController::pushAnnouncement / dismissAnnouncementFromAdmin).
     * `id` is NOT auto-generated here — it's set explicitly to match the
     * admin app's own system_announcements.id, so a later push/delete for
     * the same announcement is an update, not a duplicate.
     */
    public function up(): void
    {
        if (! Schema::hasTable('system_announcements')) {
            Schema::create('system_announcements', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->string('title');
                $table->text('body');
                $table->enum('level', ['info', 'warning', 'critical'])->default('info');
                $table->boolean('dismissible')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }

        // Per-committee-member "Ignore" — an announcement can be shown
        // to committee members across many different communities on
        // this instance, and each person dismisses it independently.
        if (! Schema::hasTable('system_announcement_dismissals')) {
            Schema::create('system_announcement_dismissals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('system_announcement_id');
                $table->unsignedBigInteger('committee_id');
                $table->timestamp('dismissed_at');

                $table->foreign('system_announcement_id')->references('id')->on('system_announcements')->cascadeOnDelete();
                $table->unique(['system_announcement_id', 'committee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_announcement_dismissals');
        Schema::dropIfExists('system_announcements');
    }
};
