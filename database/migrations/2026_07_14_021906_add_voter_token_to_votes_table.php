<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('votes_poll_id_student_id_unique');
        });

        DB::statement('ALTER TABLE votes ALTER COLUMN student_id DROP NOT NULL');

        Schema::table('votes', function (Blueprint $table) {
            $table->string('voter_token', 100)->nullable()->after('student_id');
        });

        DB::statement('CREATE UNIQUE INDEX votes_poll_voter_token_unique ON votes (poll_id, voter_token) WHERE voter_token IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS votes_poll_voter_token_unique');

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('voter_token');
        });

        DB::statement('ALTER TABLE votes ALTER COLUMN student_id SET NOT NULL');

        DB::statement('CREATE UNIQUE INDEX votes_poll_id_student_id_unique ON votes (poll_id, student_id)');
    }
};
