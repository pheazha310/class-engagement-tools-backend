<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Game Result Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
        }
        h1 {
            font-size: 22px;
            margin-bottom: 8px;
        }
        .meta {
            margin-bottom: 16px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <h1>Game Result Report</h1>
    <div class="meta">
        <p><strong>Game Type:</strong> {{ $history->game_type }}</p>
        <p><strong>Started At:</strong> {{ $history->started_at?->toDateTimeString() ?? '-' }}</p>
        <p><strong>Ended At:</strong> {{ $history->ended_at?->toDateTimeString() ?? '-' }}</p>
        <p><strong>Total Questions:</strong> {{ $history->total_questions }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Participant</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $entry)
                <tr>
                    <td>{{ $entry['participant'] ?? 'Unknown' }}</td>
                    <td>{{ $entry['score'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
