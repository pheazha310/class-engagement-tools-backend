<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18pt;
            color: #4f46e5;
            margin: 0 0 5px 0;
        }
        .header .subtitle {
            font-size: 9pt;
            color: #666;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary {
            margin-bottom: 25px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .summary .stat {
            display: inline-block;
            margin-right: 30px;
        }
        .summary .stat-label {
            font-size: 8pt;
            color: #666;
        }
        .summary .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #4f46e5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #4f46e5;
            color: white;
            font-size: 9pt;
            padding: 8px 6px;
            text-align: left;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .pass {
            color: #059669;
            font-weight: bold;
        }
        .fail {
            color: #dc2626;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Generated on {{ now()->format('F j, Y, g:i a') }}</div>
    </div>

    <div class="summary">
        <div class="info-row">
            <div class="stat">
                <div class="stat-label">Total Submissions</div>
                <div class="stat-value">{{ $submissions->count() }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Passing Score</div>
                <div class="stat-value">{{ $passingScore }}%</div>
            </div>
            <div class="stat">
                <div class="stat-label">Passed</div>
                <div class="stat-value" style="color: #059669;">{{ $submissions->where('status', 'pass')->count() }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Failed</div>
                <div class="stat-value" style="color: #dc2626;">{{ $submissions->where('status', 'fail')->count() }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Avg Score</div>
                <div class="stat-value">{{ $submissions->count() > 0 ? number_format($submissions->avg('percentage'), 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    @if($submissions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Score</th>
                    <th>Percentage</th>
                    <th>Time Taken</th>
                    <th>Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $index => $submission)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $submission->student_name }}</td>
                        <td>{{ $submission->class_name ?? '-' }}</td>
                        <td>{{ $submission->score }}</td>
                        <td>{{ number_format($submission->percentage, 1) }}%</td>
                        <td>{{ gmdate('i:s', $submission->time_taken) }}</td>
                        <td>{{ $submission->submitted_at?->format('M d, Y') }}</td>
                        <td class="{{ $submission->status }}">{{ ucfirst($submission->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #999; margin-top: 40px;">No submissions found for this quiz.</p>
    @endif

    <div class="footer">
        Class Engagement Tools — Quiz Report
    </div>
</body>
</html>
