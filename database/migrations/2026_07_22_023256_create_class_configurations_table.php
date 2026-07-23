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
        Schema::create('class_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->string('subject');
            $table->string('grade_level');
            $table->string('teacher_id');
            $table->json('settings');
            $table->boolean('is_active')->default(true);
            $table->string('slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_configurations');
    }
};
