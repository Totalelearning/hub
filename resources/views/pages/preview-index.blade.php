<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Index</title>
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            margin: 2rem;
            line-height: 1.4;
            color: #111827;
            background: #f9fafb;
        }
        h1 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
        }
        .meta {
            margin: 0 0 1rem 0;
            color: #4b5563;
        }
        .list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.5rem;
        }
        .item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.625rem 0.75rem;
        }
        a {
            color: #0f766e;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        code {
            color: #334155;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <h1>Template Preview Index</h1>
    <p class="meta">Total previews: {{ $previews->count() }}</p>

    <ul class="list">
        @foreach ($previews as $preview)
            <li class="item">
                <a href="{{ $preview['url'] }}">{{ $preview['name'] }}</a>
                <code>{{ $preview['url'] }}</code>
            </li>
        @endforeach
    </ul>
</body>
</html>
