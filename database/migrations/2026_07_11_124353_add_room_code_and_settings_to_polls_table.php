<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->string('room_code', 6)->unique()->nullable()->after('question');
            $table->boolean('is_multiple_choice')->default(false)->after('status');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('is_multiple_choice');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['room_code', 'is_multiple_choice', 'duration_minutes']);
        });
    }
};
