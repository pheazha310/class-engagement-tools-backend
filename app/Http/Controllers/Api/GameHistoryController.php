<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameHistoryResource;
use App\Models\GameSession;
use App\Repositories\Contracts\GameHistoryRepositoryInterface;
use App\Services\GameResultExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class GameHistoryController extends Controller
{
    public function __construct(
        private readonly GameHistoryRepositoryInterface $gameHistoryRepository,
        private readonly GameResultExportService $exportService
    ) {}

    public function index(): JsonResponse
    {
        $user = Auth::user();

        $perPage = (int) request()->query('per_page', 10);

        if ($user) {
            $histories = $this->gameHistoryRepository->findByTeacher($user->id, $perPage);
        } else {
            $histories = $this->gameHistoryRepository->findAllPaginated($perPage);
        }

        return response()->json([
            'data' => GameHistoryResource::collection($histories),
            'meta' => [
                'total' => $histories->total(),
                'per_page' => $histories->perPage(),
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        $history = $this->gameHistoryRepository->findById($id);

        if (! $history) {
            return response()->json([
                'message' => 'Game history not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if ($user && $history->teacher_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'data' => new GameHistoryResource($history),
        ], Response::HTTP_OK);
    }

    public function export(int $id, string $format): SymfonyResponse
    {
        $history = $this->gameHistoryRepository->findById($id);

        if (! $history) {
            return response()->json([
                'message' => 'Game history not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if (! $user || $history->teacher_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        $gameSession = $history->gameSession;

        if ($gameSession instanceof GameSession && $gameSession->status !== 'ended') {
            return response()->json([
                'message' => 'Only completed games can be exported.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $format = strtolower($format);

        if ($format === 'csv') {
            $content = $this->exportService->exportCsv($history);
            $fileName = "game-result-{$id}.csv";

            return response($content, Response::HTTP_OK, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Content-Length' => strlen($content),
            ]);
        }

        if ($format === 'pdf') {
            $content = $this->exportService->exportPdf($history);
            $fileName = "game-result-{$id}.pdf";

            return response($content, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Content-Length' => strlen($content),
            ]);
        }

        return response()->json([
            'message' => 'Invalid export format. Supported formats: csv, pdf.',
        ], Response::HTTP_BAD_REQUEST);
    }
}
