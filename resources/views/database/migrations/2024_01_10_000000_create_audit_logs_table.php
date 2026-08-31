<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only: no update/delete routes are ever exposed for this
     * table anywhere in the app. It exists purely as a record of who
     * did what, when.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('committee_name')->nullable(); // denormalized snapshot at time of action
            $table->string('action'); // created | updated | deactivated | activated | archived | restored
            $table->string('subject_type'); // e.g. "Resident", "Payment"
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
