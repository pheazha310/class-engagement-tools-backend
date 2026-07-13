<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheels', function (Blueprint $table) {
            $table->uuid('theme_id')->nullable()->after('share_token');

            $table->foreign('theme_id')
                ->references('id')
                ->on('wheel_themes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wheels', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->dropColumn('theme_id');
        });
    }
};
