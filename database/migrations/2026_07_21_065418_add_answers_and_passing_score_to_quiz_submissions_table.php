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
        Schema::table('quiz_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_submissions', 'answers')) {
                $table->json('answers')->nullable()->after('class_name');
            }
            if (! Schema::hasColumn('quiz_submissions', 'total_points')) {
                $table->integer('total_points')->default(0)->after('score');
            }
            if (! Schema::hasColumn('quiz_submissions', 'passing_score')) {
                $table->integer('passing_score')->default(50)->after('percentage');
            }

            $table->index(['quiz_id', 'student_name'], 'idx_submissions_quiz_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn(['answers', 'total_points', 'passing_score']);
            $table->dropIndex('idx_submissions_quiz_student');
        });
    }
};
