<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('id');
            $table->string('country')->default('Cambodia')->after('school_name');
            $table->string('province')->nullable()->after('country');
        });

        DB::table('schools')->update([
            'school_name' => DB::raw('COALESCE(name, school_name)'),
            'country' => 'Cambodia',
        ]);

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['name', 'province_id']);
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->foreignId('province_id')->nullable()->after('name');
        });

        DB::table('schools')->update([
            'name' => DB::raw('school_name'),
        ]);

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'country', 'province']);
        });
    }
};
