<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
            $table->dropIndex(['village_id']);
            $table->dropColumn('village_id');
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->index('district_id');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
            $table->dropColumn('commune_id');
            $table->dropForeign(['village_id']);
            $table->dropColumn('village_id');
        });

        Schema::dropIfExists('villages');
        Schema::dropIfExists('communes');
    }

    public function down(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->index('district_id');
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->index('commune_id');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropIndex(['district_id']);
            $table->dropColumn('district_id');
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
            $table->index('village_id');
        });
    }
};
