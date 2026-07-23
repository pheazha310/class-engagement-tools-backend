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
        Schema::create('teacher_activities', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->string('teacher_id');
            $table->string('activity_type');
            $table->string('activity_name');
            $table->timestamp('timestamp');
            $table->integer('participants');
            $table->string('status');
            $table->decimal('score', 8, 2);
            $table->text('details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_activities');
    }
};
