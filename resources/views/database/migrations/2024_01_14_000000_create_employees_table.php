<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('id_number')->unique();
            $table->string('role');
            $table->decimal('salary', 12, 2);
            $table->date('payment_date')->nullable(); // the recurring date salary is expected to be paid (e.g. 28th of each month)
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
