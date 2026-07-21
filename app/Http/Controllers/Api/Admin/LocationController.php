<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->value();

        $locations = Location::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('school_name', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%");
                });
            })
            ->orderBy('school_name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Location $location): array => [
                'id' => $location->id,
                'school_name' => $location->school_name,
                'country' => $location->country,
                'province' => $location->province,
                'created_at' => $location->created_at,
                'updated_at' => $location->updated_at,
            ]);

        return response()->json($locations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'School created.',
            'location' => $location,
        ], 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json([
            'id' => $location->id,
            'school_name' => $location->school_name,
            'country' => $location->country,
            'province' => $location->province,
            'created_at' => $location->created_at,
            'updated_at' => $location->updated_at,
        ]);
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'School updated.',
            'location' => [
                'id' => $location->id,
                'school_name' => $location->school_name,
                'country' => $location->country,
                'province' => $location->province,
                'created_at' => $location->created_at,
                'updated_at' => $location->updated_at,
            ],
        ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return response()->json([
            'message' => 'School deleted.',
        ]);
    }

    public function lookupData(): JsonResponse
    {
        $countries = Country::orderBy('name')->pluck('name');

        $provinces = Province::select('provinces.name', 'countries.name as country')
            ->join('countries', 'provinces.country_id', '=', 'countries.id')
            ->orderBy('provinces.name')
            ->get()
            ->map(fn ($item): array => [
                'name' => $item->name,
                'country' => $item->country,
            ]);

        return response()->json([
            'countries' => $countries,
            'provinces' => $provinces,
        ]);
    }
}
