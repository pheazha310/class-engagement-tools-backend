<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');

        Schema::create('polls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('question');
            $table->string('poll_type'); // multiple_choice, yes_no, rating
            $table->string('status')->default('draft'); // draft, active, closed
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('allow_multiple_votes')->default(false);
            $table->boolean('anonymous')->default(true);
            $table->boolean('show_results')->default(true);
            $table->string('public_token', 64)->unique();
            $table->uuid('created_by');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->index('created_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
