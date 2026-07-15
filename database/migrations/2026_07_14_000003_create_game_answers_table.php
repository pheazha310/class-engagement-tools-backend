<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->string('user_id')->nullable();
            $table->string('question_id')->nullable();
            $table->text('submitted_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->string('participant_name')->nullable();
            $table->timestamps();

            $table->index(['game_session_id', 'user_id']);
            $table->index(['game_session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_answers');
    }
};
