<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change()->constrained()->nullOnDelete();
            $table->string('email')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('school_requests', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change()->constrained()->cascadeOnDelete();
        });
    }
};
