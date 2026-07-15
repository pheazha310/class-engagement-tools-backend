<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuizReportController extends Controller
{
    public function exportPdf(Request $request, Quiz $quiz)
    {
        $submissions = $this->getFilteredSubmissions($request, $quiz);

        $quizTitle = $quiz->title;
        $passingScore = $quiz->passing_score ?? 50;

        $data = [
            'quiz' => $quiz,
            'submissions' => $submissions,
            'title' => "Quiz Report: {$quizTitle}",
            'passingScore' => $passingScore,
        ];

        $pdf = Pdf::loadView('reports.quiz-pdf', $data);

        return $pdf->download("quiz-report-{$quiz->id}.pdf");
    }

    public function exportExcel(Request $request, Quiz $quiz)
    {
        $submissions = $this->getFilteredSubmissions($request, $quiz);

        $quizTitle = $quiz->title;

        // Build CSV content
        $headers = ['Student Name', 'Score', 'Percentage', 'Time Taken (s)', 'Submission Date', 'Status', 'Class'];
        $rows = $submissions->map(function (QuizSubmission $submission) {
            return [
                $submission->student_name,
                $submission->score,
                number_format($submission->percentage, 1),
                $submission->time_taken,
                $submission->submitted_at?->format('Y-m-d H:i:s'),
                ucfirst($submission->status),
                $submission->class_name ?? '',
            ];
        });

        $csv = $this->arrayToCsv($headers, $rows->toArray());

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quiz-report-'.$quiz->id.'.csv"',
        ]);
    }

    private function getFilteredSubmissions(Request $request, Quiz $quiz)
    {
        $query = QuizSubmission::where('quiz_id', $quiz->id);

        if ($class_name = $request->input('class_name')) {
            $query->where('class_name', $class_name);
        }

        if ($date_from = $request->input('date_from')) {
            $query->where('submitted_at', '>=', $date_from);
        }

        if ($date_to = $request->input('date_to')) {
            $query->where('submitted_at', '<=', $date_to);
        }

        if ($student_name = $request->input('student_name')) {
            $query->where('student_name', 'like', "%{$student_name}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query->orderBy('submitted_at')->get();
    }

    private function arrayToCsv(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
