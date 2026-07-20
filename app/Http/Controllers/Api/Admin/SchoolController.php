<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->value();

        $schools = School::query()
            ->with('country', 'province')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('school_name', 'like', "%{$search}%")
                        ->orWhereHas('country', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('province', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('school_name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (School $school): array => [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'country_id' => $school->country_id,
                'country' => $school->country?->name,
                'province_id' => $school->province_id,
                'province' => $school->province?->name,
                'created_at' => $school->created_at,
                'updated_at' => $school->updated_at,
            ]);

        return response()->json($schools);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'province_id' => ['required', 'exists:provinces,id'],
        ]);

        $school = School::create($validated);
        $school->load('country', 'province');

        return response()->json([
            'message' => 'School created.',
            'school' => [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'country_id' => $school->country_id,
                'country' => $school->country?->name,
                'province_id' => $school->province_id,
                'province' => $school->province?->name,
                'created_at' => $school->created_at,
                'updated_at' => $school->updated_at,
            ],
        ], 201);
    }

    public function show(School $school): JsonResponse
    {
        $school->load('country', 'province');

        return response()->json([
            'id' => $school->id,
            'school_name' => $school->school_name,
            'country_id' => $school->country_id,
            'country' => $school->country?->name,
            'province_id' => $school->province_id,
            'province' => $school->province?->name,
            'created_at' => $school->created_at,
            'updated_at' => $school->updated_at,
        ]);
    }

    public function update(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'province_id' => ['required', 'exists:provinces,id'],
        ]);

        $school->update($validated);
        $school->load('country', 'province');

        return response()->json([
            'message' => 'School updated.',
            'school' => [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'country_id' => $school->country_id,
                'country' => $school->country?->name,
                'province_id' => $school->province_id,
                'province' => $school->province?->name,
                'created_at' => $school->created_at,
                'updated_at' => $school->updated_at,
            ],
        ]);
    }

    public function destroy(School $school): JsonResponse
    {
        $school->delete();

        return response()->json([
            'message' => 'School deleted.',
        ]);
    }

    public function lookupData(): JsonResponse
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'code']);

        $provinces = Province::with('country')
            ->orderBy('name')
            ->get()
            ->map(fn ($province): array => [
                'id' => $province->id,
                'name' => $province->name,
                'country_id' => $province->country_id,
            ]);

        return response()->json([
            'countries' => $countries,
            'provinces' => $provinces,
        ]);
    }
}
