<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            if (! Schema::hasColumn('votes', 'points')) {
                $table->integer('points')->nullable()->after('guest_token');
            }
            if (! Schema::hasColumn('votes', 'text_response')) {
                $table->text('text_response')->nullable()->after('points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['points', 'text_response']);
        });
    }
};
