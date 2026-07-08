<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Wheel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class WheelController extends Controller
{
    public function index(): JsonResponse
    {
        $wheels = auth()->user()->wheels()->with('participants')->get();

        return response()->json($wheels, Response::HTTP_OK);
    }

    public function store(StoreWheelRequest $request): JsonResponse
    {
        $wheel = auth()->user()->wheels()->create($request->validated());

        return response()->json($wheel->load('participants'), Response::HTTP_CREATED);
    }

    public function show(Wheel $wheel): JsonResponse
    {
        Gate::authorize('view', $wheel);

        return response()->json($wheel->load('participants'), Response::HTTP_OK);
    }

    public function update(UpdateWheelRequest $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $wheel->update($request->validated());

        return response()->json($wheel->load('participants'), Response::HTTP_OK);
    }

    public function destroy(Wheel $wheel): JsonResponse
    {
        Gate::authorize('delete', $wheel);

        $wheel->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function storeParticipant(StoreParticipantRequest $request, Wheel $wheel): JsonResponse
    {
        Gate::authorize('update', $wheel);

        $participant = $wheel->participants()->create($request->validated());

        return response()->json($participant, Response::HTTP_CREATED);
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
