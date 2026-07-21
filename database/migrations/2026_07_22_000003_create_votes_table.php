<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('poll_id');
            $table->uuid('poll_option_id');
            $table->uuid('user_id')->nullable();
            $table->string('guest_token', 64)->nullable();
            $table->timestamps();

            $table->foreign('poll_id')->references('id')->on('polls')->cascadeOnDelete();
            $table->foreign('poll_option_id')->references('id')->on('poll_options')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('poll_id');
            $table->index('poll_option_id');
            $table->index('user_id');
            $table->index('guest_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
