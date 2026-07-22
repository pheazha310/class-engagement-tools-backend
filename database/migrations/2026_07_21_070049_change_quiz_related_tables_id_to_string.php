<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL requires dropping foreign keys before changing column types
        // Drop foreign keys referencing quizzes.id
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
            });
        }

        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
            });
        }

        // Change quizzes.id from uuid to string
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('id', 255)->change();
        });

        // Change questions.id and questions.quiz_id from uuid to string
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('id', 255)->change();
                $table->string('quiz_id', 255)->change();
            });

            Schema::table('questions', function (Blueprint $table) {
                $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            });
        }

        // Change quiz_submissions.quiz_id from uuid to string
        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->string('quiz_id', 255)->change();
            });

            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop foreign keys
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
            });
        }

        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
            });
        }

        // Change back to uuid (cast via using raw expression for PostgreSQL)
        Schema::table('quizzes', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->uuid('id')->change();
                $table->uuid('quiz_id')->change();
            });

            Schema::table('questions', function (Blueprint $table) {
                $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->uuid('quiz_id')->change();
            });

            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            });
        }
    }
};
