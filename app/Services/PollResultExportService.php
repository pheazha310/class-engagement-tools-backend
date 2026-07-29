<?php

namespace App\Services;

use App\Models\Poll;
use Dompdf\Dompdf;

readonly class PollResultExportService
{
    public function exportCsv(Poll $poll): string
    {
        $poll->loadMissing('options.votes');

        $rows = [];
        $rows[] = $this->csvLine(['Question', $poll->question]);
        $rows[] = $this->csvLine(['Title', $poll->title]);
        $rows[] = $this->csvLine(['Poll Type', $poll->poll_type]);
        $rows[] = $this->csvLine(['Anonymous', $poll->anonymous ? 'Yes' : 'No']);
        $rows[] = $this->csvLine(['Status', $poll->status]);
        $rows[] = $this->csvLine(['Total Votes', (string) $poll->votes()->count()]);
        $rows[] = "\n";
        $rows[] = $this->csvLine(['Option', 'Votes', 'Percentage']);

        $totalVotes = max(1, $poll->votes()->count());

        foreach ($poll->options->sortBy('display_order') as $option) {
            $votes = $option->votes->count();
            $percentage = round(($votes / $totalVotes) * 100);

            $rows[] = $this->csvLine([
                $option->option_text,
                (string) $votes,
                $percentage.'%',
            ]);
        }

        return implode('', $rows);
    }

    public function exportPdf(Poll $poll): string
    {
        $poll->loadMissing('options.votes');

        $html = view('exports.poll-result', [
            'poll' => $poll,
            'totalVotes' => $poll->votes()->count(),
        ])->render();

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /** @param list<string> $fields */
    private function csvLine(array $fields): string
    {
        $escaped = array_map(static function (string $field): string {
            $value = $field;

            if (str_starts_with($value, '=') || str_starts_with($value, '+') || str_starts_with($value, '-') || str_starts_with($value, '@') || str_starts_with($value, "\t") || str_starts_with($value, "\r")) {
                $value = "'".$value;
            }

            if (str_contains($value, '"') || str_contains($value, ',') || str_contains($value, "\n")) {
                $value = '"'.str_replace('"', '""', $value).'"';
            }

            return $value;
        }, $fields);

        return implode(',', $escaped)."\n";
    }
}
