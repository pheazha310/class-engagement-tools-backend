<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationSchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required_without:province|exists:provinces,id',
            'province' => 'required_without:province_id|string|max:255',
            'search' => 'nullable|string|max:255',
        ]);

        $province = $request->input('province');

        if ($request->filled('province_id')) {
            $provinceModel = Province::find($request->integer('province_id'));
            $province = $provinceModel?->name;
        }

        $query = Location::query()->where('province', $province);

        if ($request->filled('search')) {
            $query->where('school_name', 'like', '%'.$request->input('search').'%');
        }

        $locations = $query->orderBy('school_name')->get();

        return response()->json(
            $locations->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->school_name,
                'address' => null,
                'latitude' => null,
                'longitude' => null,
            ])
        );
    }
}
