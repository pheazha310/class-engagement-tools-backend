<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportParticipantsRequest;
use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Wheel;
use App\Models\WheelTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class WheelController extends Controller
{
    public function index(): JsonResponse
    {
        $wheels = auth()->user()->wheels()->with(['participants', 'theme'])->get();

        return response()->json($wheels, Response::HTTP_OK);
    }

    public function store(StoreWheelRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['theme_id'])) {
            $data['theme_id'] = WheelTheme::where('is_default', true)->first()?->id;
        }

        $wheel = auth()->user()->wheels()->create($data);

        return response()->json($wheel->load(['participants', 'theme']), Response::HTTP_CREATED);
    }

    public function generateShareToken(Request $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $plaintext = $wheel->generateShareToken();

        return response()->json([
            'share_token' => $plaintext,
            'shared_url' => url("/api/wheels/shared/{$plaintext}"),
        ], Response::HTTP_CREATED);
    }

    public function showShared(string $shareToken): JsonResponse
    {
        $wheel = Wheel::findByShareToken($shareToken);

        if (! $wheel) {
            abort(Response::HTTP_NOT_FOUND, 'Shared wheel not found');
        }

        return response()->json($wheel->load(['participants', 'theme']), Response::HTTP_OK);
    }

    public function show(Wheel $wheel): JsonResponse
    {
        Gate::authorize('view', $wheel);

        return response()->json($wheel->load(['participants', 'theme']), Response::HTTP_OK);
    }

    public function update(UpdateWheelRequest $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $wheel->update($request->validated());

        return response()->json($wheel->load(['participants', 'theme']), Response::HTTP_OK);
    }

    public function destroy(Wheel $wheel): JsonResponse
    {
        Gate::authorize('delete', $wheel);

        $wheel->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function parseImportFile(string $content, string $extension): array
    {
        $names = [];

        if ($extension === 'csv') {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $row = str_getcsv($line);
                foreach ($row as $value) {
                    $value = trim($value);
                    if ($value !== '') {
                        $names[] = $value;
                    }
                }
            }
        } else {
            $lines = preg_split('/\r\n|\r|\n/', $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $names[] = $line;
                }
            }
        }

        return $names;
    }

    public function storeParticipant(StoreParticipantRequest $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $participant = $wheel->participants()->create($request->validated());

        return response()->json($participant, Response::HTTP_CREATED);
    }

    public function importParticipants(ImportParticipantsRequest $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $uploadedFile = $request->validated('file');
        $content = file_get_contents($uploadedFile->getRealPath());
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        $names = $this->parseImportFile($content, $extension);

        $existingNames = $wheel->participants()
            ->pluck('name')
            ->map(fn ($name) => mb_strtolower(trim($name)))
            ->toArray();

        $uniqueNames = array_values(array_unique($names));
        $imported = [];
        $skipped = [];

        foreach ($uniqueNames as $name) {
            $normalized = mb_strtolower(trim($name));

            if ($normalized === '') {
                $skipped[] = ['name' => $name, 'reason' => 'Empty name'];

                continue;
            }

            if (in_array($normalized, $existingNames, true)) {
                $skipped[] = ['name' => $name, 'reason' => 'Duplicate'];

                continue;
            }

            $imported[] = $wheel->participants()->create(['name' => trim($name)]);
            $existingNames[] = $normalized;
        }

        return response()->json([
            'imported' => $imported,
            'skipped' => $skipped,
            'count' => count($imported),
        ], Response::HTTP_CREATED);
    }

    public function destroyParticipant(Wheel $wheel, string $participant): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $model = $wheel->participants()->findOrFail($participant);
        $model->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function spin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participants' => ['array'],
            'participants.*.id' => ['nullable'],
            'participants.*.name' => ['required_without:wheel_id', 'string', 'max:255'],
            'wheel_id' => ['nullable', 'uuid', 'exists:wheels,id'],
            'removal_mode' => ['boolean'],
        ]);

        $frontendParticipants = collect($validated['participants'] ?? []);

        if ($frontendParticipants->isEmpty()) {
            abort(404, 'No participants available to spin');
        }

        $response = [];

        if (! empty($validated['wheel_id'])) {
            $wheel = Wheel::findOrFail($validated['wheel_id']);
            Gate::authorize('update', $wheel);

            $query = $wheel->participants()->getQuery();

            if ($query->count() === 0) {
                abort(404, 'No participants available to spin');
            }

            $selected = $query->inRandomOrder()->first();

            $history = $wheel->spinHistories()->create([
                'participant_id' => $selected->id,
                'participant_name' => $selected->name,
            ]);

            if ($validated['removal_mode'] ?? false) {
                $wheel->participants()->where('id', $selected->id)->delete();
            }

            $response['participant'] = [
                'id' => $selected->id,
                'name' => $selected->name,
            ];
            $response['history'] = $history;
        } else {
            $selected = $frontendParticipants[random_int(0, $frontendParticipants->count() - 1)];

            $response['participant'] = $selected;
        }

        return response()->json($response, Response::HTTP_OK);
    }
}
