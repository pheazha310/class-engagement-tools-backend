<?php

use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('school_name');
            $table->foreignId('province_id')->nullable()->after('country_id');
        });

        $country = Country::firstOrCreate(
            ['code' => 'KH'],
            ['name' => 'Cambodia'],
        );

        DB::table('schools')->orderBy('id')->each(function ($school) use ($country) {
            $data = [];

            if (! empty($school->country)) {
                $matched = Country::where('name', $school->country)->first();
                $data['country_id'] = $matched?->id ?? $country->id;
            } else {
                $data['country_id'] = $country->id;
            }

            if (! empty($school->province)) {
                $province = Province::firstOrCreate(
                    ['name' => $school->province],
                    ['country_id' => $data['country_id']],
                );
                $data['province_id'] = $province->id;
            }

            if ($data !== []) {
                DB::table('schools')->where('id', $school->id)->update($data);
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['country', 'province']);
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('country')->default('Cambodia');
            $table->string('province')->nullable();
        });

        DB::table('schools')->orderBy('id')->each(function ($school) {
            $data = [];

            if (! empty($school->country_id)) {
                $country = Country::find($school->country_id);
                $data['country'] = $country?->name ?? 'Cambodia';
            } else {
                $data['country'] = 'Cambodia';
            }

            if (! empty($school->province_id)) {
                $province = Province::find($school->province_id);
                $data['province'] = $province?->name;
            }

            if ($data !== []) {
                DB::table('schools')->where('id', $school->id)->update($data);
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['province_id']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'province_id']);
        });
    }
};
