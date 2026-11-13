<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #25313c
        }

        h1 {
            color: #123b5d
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #dbe3ec;
            padding: 7px;
            text-align: left
        }

        th {
            background: #eef3f7
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <table>@if(!empty($data['rows']))
        <thead>
            <tr>@foreach(array_keys((array) $data['rows'][0]) as $h)
            <th>{{ ucwords(str_replace('_', ' ', $h)) }}</th>@endforeach
            </tr>
        </thead>
        <tbody>@foreach($data['rows'] as $row)
            <tr>@foreach((array) $row as $value)
            <td>{{ is_scalar($value) ? $value : '' }}</td>@endforeach
        </tr>@endforeach
    </tbody>@else<tr>
            <td>No records found</td>
        </tr>@endif
    </table>
</body>

</html>