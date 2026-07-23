<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('audio_url');
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sounds');
    }
};
