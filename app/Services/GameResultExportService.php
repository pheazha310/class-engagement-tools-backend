<?php

namespace App\Services;

use App\Models\GameHistory;
use Dompdf\Dompdf;

readonly class GameResultExportService
{
    public function exportCsv(GameHistory $history): string
    {
        $participants = $history->scores ?? [];
        $lines = [];

        $lines[] = $this->csvLine(['Participant', 'Score', 'Total Questions', 'Game Type', 'Started At', 'Ended At']);

        foreach ($participants as $entry) {
            $lines[] = $this->csvLine([
                (string) ($entry['participant'] ?? 'Unknown'),
                (string) ($entry['score'] ?? 0),
                (string) $history->total_questions,
                (string) $history->game_type,
                (string) ($history->started_at?->toDateTimeString() ?? ''),
                (string) ($history->ended_at?->toDateTimeString() ?? ''),
            ]);
        }

        return implode('', $lines);
    }

    /** @param list<string> $fields */
    private function csvLine(array $fields): string
    {
        $escaped = array_map(function ($field) {
            $value = (string) $field;

            if (str_starts_with($value, '=') || str_starts_with($value, '+') || str_starts_with($value, '-') || str_starts_with($value, '@') || str_starts_with($value, "\t") || str_starts_with($value, "\r")) {
                $value = "'".$value;
            }

            if (str_contains($value, '"') || str_contains($value, ',') || str_contains($value, "\n")) {
                $value = str_replace('"', '""', $value);
                $value = '"'.$value.'"';
            }

            return $value;
        }, $fields);

        return implode(',', $escaped)."\n";
    }

    public function exportPdf(GameHistory $history): string
    {
        $participants = $history->scores ?? [];

        $html = view('exports.game-result', [
            'history' => $history,
            'participants' => $participants,
        ])->render();

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
