<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transcript Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        th.table-heading-cell {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('transcripts.index') }}">Transcript Manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('transcripts.index') }}">Transcripts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('transcripts.import') }}">Import</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3 text-white">
                @auth
                    <span class="small">Signed in as {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                @endauth
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
