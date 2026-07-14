<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('duration_minutes');
            $table->boolean('is_quiz')->default(false)->after('is_anonymous');
            $table->boolean('is_open_text')->default(false)->after('is_quiz');
            $table->unsignedSmallInteger('max_points')->nullable()->after('is_open_text');
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->boolean('is_correct')->default(false)->after('option_text');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->unsignedSmallInteger('points')->default(1)->after('student_id');
            $table->text('text_response')->nullable()->after('points');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->foreignId('correct_option_id')->nullable()->after('max_points')
                ->constrained('poll_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('correct_option_id');
            $table->dropColumn(['is_anonymous', 'is_quiz', 'is_open_text', 'max_points']);
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropColumn('is_correct');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['points', 'text_response']);
        });
    }
};
