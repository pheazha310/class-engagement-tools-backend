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
        Schema::create('spin_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('wheel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('participant_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_histories');
    }
};
