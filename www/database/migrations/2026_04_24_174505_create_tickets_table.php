<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_id')->constrained('services');
            $table->foreignUuid('priority_id')->constrained('priorities');
            $table->foreignUuid('totem_id')->constrained('totems');
            $table->foreignUuid('counter_id')->nullable()->constrained('counters');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->integer('number');
            $table->string('formatted_number', 20);
            $table->enum('status', ['pending', 'called', 'in_progress', 'absent', 'completed', 'cancelled'])->default('pending');
            $table->integer('absence_count')->default(0);
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
