<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transcript Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('transcripts.index') }}">Transcript Manager</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('transcripts.index') }}">Transcripts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('transcripts.import') }}">Import</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mb-5">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Please correct the highlighted issues:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.querySelector('#select-all');
        if (selectAll) {
            selectAll.addEventListener('change', (event) => {
                document.querySelectorAll('.transcript-checkbox').forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                });
            });
        }
    });
</script>
</body>
</html>
