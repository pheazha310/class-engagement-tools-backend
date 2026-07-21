<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationOptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_name' => ['required', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'province_name' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
        ]);

        $countryName = $this->cleanName($validated['country_name']);
        $provinceName = $this->cleanName($validated['province_name']);
        $schoolName = $this->cleanName($validated['school_name']);

        $country = Country::query()
            ->whereRaw('lower(name) = ?', [Str::lower($countryName)])
            ->first();

        if (! $country) {
            $country = Country::create([
                'name' => $countryName,
                'code' => $this->countryCode($countryName, $validated['country_code'] ?? null),
            ]);
        }

        $province = Province::query()
            ->where('country_id', $country->id)
            ->whereRaw('lower(name) = ?', [Str::lower($provinceName)])
            ->first();

        if (! $province) {
            $province = Province::create([
                'country_id' => $country->id,
                'name' => $provinceName,
            ]);
        }

        $school = Location::query()
            ->whereRaw('lower(country) = ?', [Str::lower($country->name)])
            ->whereRaw('lower(province) = ?', [Str::lower($province->name)])
            ->whereRaw('lower(school_name) = ?', [Str::lower($schoolName)])
            ->first();

        if (! $school) {
            $school = Location::create([
                'country' => $country->name,
                'province' => $province->name,
                'school_name' => $schoolName,
            ]);
        }

        return response()->json([
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
            ],
            'province' => [
                'id' => $province->id,
                'country_id' => $province->country_id,
                'name' => $province->name,
            ],
            'school' => [
                'id' => $school->id,
                'name' => $school->school_name,
            ],
        ], 201);
    }

    private function cleanName(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function countryCode(string $countryName, ?string $requestedCode): string
    {
        $base = Str::upper(Str::slug($requestedCode ?: $countryName, ''));
        $base = substr($base, 0, 10) ?: 'LOC';
        $code = $base;
        $suffix = 2;

        while (Country::query()->where('code', $code)->exists()) {
            $suffixText = (string) $suffix;
            $code = substr($base, 0, 10 - strlen($suffixText)).$suffixText;
            $suffix++;
        }

        return $code;
    }
}
