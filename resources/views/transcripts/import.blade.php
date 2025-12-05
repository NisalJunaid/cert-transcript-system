@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Import transcript sheet</span>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('transcripts.index') }}">View transcripts</a>
    </div>
    <div class="card-body">
        <form action="{{ route('transcripts.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Course shortcode</label>
                    <input type="text" name="course_shortcode" class="form-control" value="{{ old('course_shortcode') }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Course name</label>
                    <input type="text" name="course_name" class="form-control" value="{{ old('course_name') }}" required>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Transcript CSV</label>
                <input type="file" name="file" class="form-control" required>
                <div class="form-text">Headers may include multiple module_name_X/module_code_X groups. Delimiter is auto detected (comma, semicolon or tab).</div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>
<div class="card mt-4">
    <div class="card-header">Existing courses</div>
    <div class="card-body">
        @if($courses->isEmpty())
            <p class="text-muted mb-0">No courses found yet.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Shortcode</th>
                            <th class="text-nowrap">Name</th>
                            <th class="text-nowrap">Level</th>
                            <th class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td class="text-nowrap">{{ $course->shortcode }}</td>
                                <td>{{ $course->name }}</td>
                                <td class="text-nowrap">{{ $course->level ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this course and all related transcripts?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
