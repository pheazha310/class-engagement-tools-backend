<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->uuid('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->string('game_type');
            $table->json('settings')->nullable();
            $table->json('participants')->nullable();
            $table->json('scores')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('game_session_id');
            $table->index('teacher_id');
            $table->index('game_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_histories');
    }
};
