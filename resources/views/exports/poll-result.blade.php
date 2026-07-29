<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Poll Result Report</title>
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
    <h1>Poll Result Report</h1>
    <div class="meta">
        <p><strong>Question:</strong> {{ $poll->question }}</p>
        <p><strong>Title:</strong> {{ $poll->title }}</p>
        <p><strong>Type:</strong> {{ $poll->poll_type }}</p>
        <p><strong>Anonymous:</strong> {{ $poll->anonymous ? 'Yes' : 'No' }}</p>
        <p><strong>Status:</strong> {{ $poll->status }}</p>
        <p><strong>Total Votes:</strong> {{ $totalVotes }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Option</th>
                <th>Votes</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poll->options->sortBy('display_order') as $option)
                @php
                    $votes = $option->votes->count();
                    $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $option->option_text }}</td>
                    <td>{{ $votes }}</td>
                    <td>{{ $percentage }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
