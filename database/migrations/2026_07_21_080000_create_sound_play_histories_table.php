<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sound_play_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sound_id')->nullable()->constrained('sounds')->nullOnDelete();
            $table->string('sound_name');
            $table->string('audio_url');
            $table->string('sound_category')->nullable();
            $table->string('icon')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->foreignUuid('played_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('played_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sound_play_histories');
    }
};
