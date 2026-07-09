<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use App\Models\Country;
use App\Models\Location;
use App\Models\Province;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    private const FALLBACK_PROVINCES = [
        'Banteay Meanchey', 'Battambang', 'Kampong Cham', 'Kampong Chhnang',
        'Kampong Speu', 'Kampong Thom', 'Kampot', 'Kandal', 'Kep',
        'Koh Kong', 'Kratie', 'Mondulkiri', 'Oddar Meanchey', 'Pailin',
        'Phnom Penh', 'Preah Vihear', 'Prey Veng', 'Pursat', 'Ratanakiri',
        'Siem Reap', 'Preah Sihanouk', 'Stung Treng', 'Svay Rieng', 'Takéo',
        'Tboung Khmum',
    ];

    private function getCountries(): array
    {
        try {
            return Country::query()
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        } catch (QueryException) {
            return ['Cambodia'];
        }
    }

    private function getAllProvinces(): array
    {
        try {
            return Province::query()
                ->with('country')
                ->orderBy('name')
                ->get()
                ->map(fn (Province $province): array => [
                    'name' => $province->name,
                    'country' => $province->country?->name,
                ])
                ->toArray();
        } catch (QueryException) {
            return collect(self::FALLBACK_PROVINCES)->map(fn (string $name): array => [
                'name' => $name,
                'country' => 'Cambodia',
            ])->toArray();
        }
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $locations = Location::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('school_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Location $location): array => [
                'id' => $location->id,
                'country' => $location->country,
                'province' => $location->province,
                'school_name' => $location->school_name,
                'created_at' => $location->created_at?->toISOString(),
                'updated_at' => $location->updated_at?->toISOString(),
            ]);

        return Inertia::render('admin/locations/Index', [
            'locations' => $locations,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/locations/Create', [
            'countries' => $this->getCountries(),
            'provinces' => $this->getAllProvinces(),
        ]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Location::create([
            'country' => $validated['country'],
            'province' => $validated['province'],
            'school_name' => $validated['school_name'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location created.')]);

        return to_route('admin.locations.index');
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('admin/locations/Edit', [
            'location' => [
                'id' => $location->id,
                'country' => $location->country,
                'province' => $location->province,
                'school_name' => $location->school_name,
            ],
            'countries' => $this->getCountries(),
            'provinces' => $this->getAllProvinces(),
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $validated = $request->validated();

        $location->update([
            'country' => $validated['country'],
            'province' => $validated['province'],
            'school_name' => $validated['school_name'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location updated.')]);

        return to_route('admin.locations.index');
    }

    public function destroy(Request $request, Location $location): RedirectResponse
    {
        $location->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location deleted.')]);

        return to_route('admin.locations.index');
    }
}
