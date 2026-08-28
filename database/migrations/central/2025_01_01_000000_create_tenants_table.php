<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lives only on the 'central' connection — this is the one place
     * that knows every community that has ever signed up, regardless
     * of which SQLite file its actual data lives in.
     */
    public function up(): void
    {
        Schema::connection('central')->create('tenants', function (Blueprint $table) {
            $table->id();

            $table->string('name');                 // community display name, e.g. "Green Valley"
            $table->string('slug')->unique();        // url segment: nexora.com/{slug}
            $table->enum('community_type', ['normal', 'condo'])->default('normal');

            $table->string('owner_email');
            $table->string('db_path');               // absolute path to this tenant's sqlite file

            // provisioning -> queued, DB not ready yet
            // pending_setup -> DB ready, waiting on owner to set a password
            // active        -> owner has logged in at least once
            // failed        -> provisioning job errored, needs attention
            $table->enum('status', ['provisioning', 'pending_setup', 'active', 'failed'])
                ->default('provisioning');

            $table->string('setup_token')->nullable();     // hashed, single use
            $table->timestamp('setup_token_expires_at')->nullable();

            $table->text('provisioning_error')->nullable(); // last failure reason, if any

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenants');
    }
};
