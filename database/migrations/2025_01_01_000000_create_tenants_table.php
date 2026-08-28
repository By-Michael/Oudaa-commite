<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The one registry of every community that has ever signed up.
     * No db_path anymore — there's no separate file to point at.
     * Every other table just carries a community_id (see the
     * add_community_id migration) pointing back at this table's id.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            $table->string('name');                 // community display name, e.g. "Green Valley"
            $table->string('slug')->unique();        // url segment: nexora.com/{slug}
            $table->enum('community_type', ['normal', 'condo'])->default('normal');

            $table->string('owner_email');

            // pending_setup -> row created, waiting on owner to set a password
            // active        -> owner has logged in at least once
            // failed        -> provisioning threw (e.g. mail send failed), needs attention
            $table->enum('status', ['pending_setup', 'active', 'failed'])
                ->default('pending_setup');

            $table->string('setup_token')->nullable();     // hashed, single use
            $table->timestamp('setup_token_expires_at')->nullable();

            $table->text('provisioning_error')->nullable(); // last failure reason, if any

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
