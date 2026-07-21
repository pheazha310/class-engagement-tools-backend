<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->string('poll_type', 20)->default('multiple_choice')->after('is_multiple_choice');
            $table->string('share_token', 32)->nullable()->unique()->after('room_code');
            $table->boolean('show_results')->default(true)->after('status');
            // Legacy boolean columns used alongside the old is_multiple_choice flag.
            // These were previously missing from the schema but referenced by the model.
            $table->boolean('is_anonymous')->default(false)->after('show_results');
            $table->boolean('is_quiz')->default(false)->after('is_anonymous');
            $table->boolean('is_open_text')->default(false)->after('is_quiz');
            $table->unsignedSmallInteger('max_points')->nullable()->after('is_open_text');
        });

        // Normalise the legacy boolean flags into the new structured type.
        DB::statement("UPDATE polls SET poll_type = 'yes_no' WHERE poll_type = 'multiple_choice' AND is_multiple_choice = false AND is_quiz = false AND is_open_text = false");
        DB::statement("UPDATE polls SET poll_type = 'open_text' WHERE is_open_text = true");
        DB::statement("UPDATE polls SET poll_type = 'quiz' WHERE is_quiz = true");
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn([
                'poll_type',
                'share_token',
                'show_results',
                'is_anonymous',
                'is_quiz',
                'is_open_text',
                'max_points',
            ]);
        });
    }
};
