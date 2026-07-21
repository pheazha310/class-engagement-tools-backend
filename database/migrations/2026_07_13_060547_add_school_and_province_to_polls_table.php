<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('teacher_id')->constrained()->nullOnDelete();
            $table->foreignId('province_id')->nullable()->after('school_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
