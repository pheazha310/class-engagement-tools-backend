<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Wheel;
use Illuminate\Http\JsonResponse;
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
}
