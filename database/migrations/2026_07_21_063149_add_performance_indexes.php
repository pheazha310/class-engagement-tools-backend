<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add critical database indexes for frequently-queried columns, skipping any
     * that already exist (e.g. foreign key indexes created by earlier migrations).
     *
     * These indexes dramatically speed up:
     * - Teacher dashboard queries (teacher_id / user_id lookups)
     * - Poll/quiz status filtering
     * - Join code / room code lookups
     * - Vote/submission date-range queries
     * - Permission checks
     */
    public function up(): void
    {
        $this->addIndexIfMissing('polls', 'polls_teacher_id_index', ['teacher_id']);
        $this->addIndexIfMissing('polls', 'polls_status_index', ['status']);
        $this->addIndexIfMissing('polls', 'polls_room_code_index', ['room_code']);
        $this->addIndexIfMissing('polls', 'polls_created_at_index', ['created_at']);

        $this->addIndexIfMissing('poll_options', 'poll_options_poll_id_index', ['poll_id']);

        $this->addIndexIfMissing('votes', 'votes_poll_id_index', ['poll_id']);
        $this->addIndexIfMissing('votes', 'votes_student_id_index', ['student_id']);
        $this->addIndexIfMissing('votes', 'votes_option_id_index', ['option_id']);
        $this->addIndexIfMissing('votes', 'votes_created_at_index', ['created_at']);

        $this->addIndexIfMissing('quizzes', 'quizzes_teacher_id_index', ['teacher_id']);
        $this->addIndexIfMissing('quizzes', 'quizzes_status_index', ['status']);
        $this->addIndexIfMissing('quizzes', 'quizzes_created_at_index', ['created_at']);

        $this->addIndexIfMissing('questions', 'questions_quiz_id_index', ['quiz_id']);

        $this->addIndexIfMissing('quiz_submissions', 'quiz_submissions_quiz_id_index', ['quiz_id']);
        $this->addIndexIfMissing('quiz_submissions', 'quiz_submissions_student_name_index', ['student_name']);
        $this->addIndexIfMissing('quiz_submissions', 'quiz_submissions_created_at_index', ['created_at']);

        $this->addIndexIfMissing('game_sessions', 'game_sessions_teacher_id_index', ['teacher_id']);
        $this->addIndexIfMissing('game_sessions', 'game_sessions_join_code_index', ['join_code']);
        $this->addIndexIfMissing('game_sessions', 'game_sessions_status_index', ['status']);
        $this->addIndexIfMissing('game_sessions', 'game_sessions_created_at_index', ['created_at']);

        $this->addIndexIfMissing('game_answers', 'game_answers_game_session_id_index', ['game_session_id']);
        $this->addIndexIfMissing('game_answers', 'game_answers_user_id_index', ['user_id']);

        $this->addIndexIfMissing('wheels', 'wheels_user_id_index', ['user_id']);
        $this->addIndexIfMissing('wheels', 'wheels_share_token_index', ['share_token']);

        $this->addIndexIfMissing('participants', 'participants_wheel_id_index', ['wheel_id']);

        $this->addIndexIfMissing('spin_histories', 'spin_histories_wheel_id_index', ['wheel_id']);

        $this->addIndexIfMissing('user_profiles', 'user_profiles_user_id_index', ['user_id']);
        $this->addIndexIfMissing('user_profiles', 'user_profiles_school_id_index', ['school_id']);

        $this->addIndexIfMissing('model_has_roles', 'model_has_roles_model_id_index', ['model_id']);
        $this->addIndexIfMissing('model_has_permissions', 'model_has_permissions_model_id_index', ['model_id']);
    }

    /**
     * Safely add an index if it doesn't already exist on the table.
     */
    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (Schema::hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns) {
            $table->index($columns);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('polls', 'polls_teacher_id_index');
        $this->dropIndexIfExists('polls', 'polls_status_index');
        $this->dropIndexIfExists('polls', 'polls_room_code_index');
        $this->dropIndexIfExists('polls', 'polls_created_at_index');

        $this->dropIndexIfExists('poll_options', 'poll_options_poll_id_index');

        $this->dropIndexIfExists('votes', 'votes_poll_id_index');
        $this->dropIndexIfExists('votes', 'votes_student_id_index');
        $this->dropIndexIfExists('votes', 'votes_option_id_index');
        $this->dropIndexIfExists('votes', 'votes_created_at_index');

        $this->dropIndexIfExists('quizzes', 'quizzes_teacher_id_index');
        $this->dropIndexIfExists('quizzes', 'quizzes_status_index');
        $this->dropIndexIfExists('quizzes', 'quizzes_created_at_index');

        $this->dropIndexIfExists('questions', 'questions_quiz_id_index');

        $this->dropIndexIfExists('quiz_submissions', 'quiz_submissions_quiz_id_index');
        $this->dropIndexIfExists('quiz_submissions', 'quiz_submissions_student_name_index');
        $this->dropIndexIfExists('quiz_submissions', 'quiz_submissions_created_at_index');

        $this->dropIndexIfExists('game_sessions', 'game_sessions_teacher_id_index');
        $this->dropIndexIfExists('game_sessions', 'game_sessions_join_code_index');
        $this->dropIndexIfExists('game_sessions', 'game_sessions_status_index');
        $this->dropIndexIfExists('game_sessions', 'game_sessions_created_at_index');

        $this->dropIndexIfExists('game_answers', 'game_answers_game_session_id_index');
        $this->dropIndexIfExists('game_answers', 'game_answers_user_id_index');

        $this->dropIndexIfExists('wheels', 'wheels_user_id_index');
        $this->dropIndexIfExists('wheels', 'wheels_share_token_index');

        $this->dropIndexIfExists('participants', 'participants_wheel_id_index');

        $this->dropIndexIfExists('spin_histories', 'spin_histories_wheel_id_index');

        $this->dropIndexIfExists('user_profiles', 'user_profiles_user_id_index');
        $this->dropIndexIfExists('user_profiles', 'user_profiles_school_id_index');

        $this->dropIndexIfExists('model_has_roles', 'model_has_roles_model_id_index');
        $this->dropIndexIfExists('model_has_permissions', 'model_has_permissions_model_id_index');
    }

    /**
     * Safely drop an index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
};
