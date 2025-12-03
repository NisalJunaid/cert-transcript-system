@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Filter transcripts</span>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('transcripts.import') }}">Import new file</a>
    </div>
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('transcripts.index') }}">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-uppercase text-muted">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Name, serial, national or student id">
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-uppercase text-muted">Course</label>
                <select name="course_id" class="form-select">
                    <option value="">Any</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($filters['course_id'] === $course->id)>{{ $course->name }} ({{ $course->shortcode }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-uppercase text-muted">Batch</label>
                <input type="text" name="batch" class="form-control" value="{{ $filters['batch'] }}">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-uppercase text-muted">Program</label>
                <input type="text" name="program" class="form-control" value="{{ $filters['program'] }}">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-uppercase text-muted">Level</label>
                <input type="text" name="level" class="form-control" value="{{ $filters['level'] }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline-secondary" href="{{ route('transcripts.index') }}">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Transcripts</div>
    <div class="card-body">
        @if($transcripts->isEmpty())
            <p class="text-muted mb-0">No transcripts found. Import a CSV to get started.</p>
        @else
            <form method="POST" action="{{ route('transcripts.pdf') }}" id="transcript-form">
                @csrf
                <input type="hidden" name="template" id="template-input" value="auto">
                <div class="row mb-3 align-items-end g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label small text-uppercase text-muted mb-1">Bulk document type</label>
                        <select name="document_type" id="document-type" class="form-select">
                            <option value="transcript" selected>Transcript</option>
                            <option value="certificate">Certificate</option>
                        </select>
                        <div class="form-text">Transcript templates are chosen automatically by course level.</div>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100" id="bulk-submit">Download Selected</button>
                    </div>
                    <div class="col-lg-5 d-flex align-items-end">
                        <p class="mb-0 text-muted">Select rows, pick the document type, then download the chosen students.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="table-heading-cell text-center"><input type="checkbox" id="select-all"></th>
                                <th class="table-heading-cell">Student</th>
                                <th class="table-heading-cell">Course</th>
                                <th class="table-heading-cell text-center">Batch</th>
                                <th class="table-heading-cell text-center">CGPA</th>
                                <th class="table-heading-cell text-center">Completed</th>
                                <th class="table-heading-cell text-center">Modules</th>
                                <th class="table-heading-cell text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transcripts as $transcript)
                                <tr>
                                    <td class="text-center"><input class="form-check-input transcript-checkbox" type="checkbox" name="transcript_ids[]" value="{{ $transcript->id }}"></td>
                                    <td class="align-middle">
                                        <div class="fw-semibold">{{ $transcript->student->name }}</div>
                                        <div class="small text-muted">ID: {{ $transcript->student->student_identifier ?? 'n/a' }} | Serial: {{ $transcript->student->certificate_serial_number ?? 'n/a' }}</div>
                                    </td>
                                    <td class="align-middle">{{ $transcript->course->name }} ({{ $transcript->course->shortcode }})</td>
                                    <td class="text-center align-middle">{{ $transcript->student->batch_no ?? 'n/a' }}</td>
                                    <td class="text-center align-middle">{{ $transcript->cgpa ?? 'n/a' }}</td>
                                    <td class="text-center align-middle">{{ $transcript->completed_date?->format('Y-m-d') ?? 'n/a' }}</td>
                                    <td class="text-center align-middle">{{ $transcript->moduleResults->count() }}</td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <button
                                                type="submit"
                                                name="transcript_ids[]"
                                                value="{{ $transcript->id }}"
                                                class="btn btn-sm btn-outline-primary document-trigger"
                                                formaction="{{ route('transcripts.pdf') }}"
                                                formmethod="POST"
                                                data-document-type="transcript"
                                            >
                                                Download Transcript
                                            </button>
                                            <button
                                                type="submit"
                                                name="transcript_ids[]"
                                                value="{{ $transcript->id }}"
                                                class="btn btn-sm btn-outline-secondary document-trigger"
                                                formaction="{{ route('transcripts.pdf') }}"
                                                formmethod="POST"
                                                data-document-type="certificate"
                                            >
                                                Download Certificate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="mt-3">
                {{ $transcripts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('transcript-form');
        const documentTypeSelect = document.getElementById('document-type');
        const templateInput = document.getElementById('template-input');

        documentTypeSelect.addEventListener('change', (event) => {
            const type = event.target.value;
            templateInput.value = type === 'certificate' ? 'certificate-award' : 'auto';
        });

        document.querySelectorAll('.document-trigger').forEach((button) => {
            button.addEventListener('click', () => {
                const targetType = button.dataset.documentType || 'transcript';
                documentTypeSelect.value = targetType;
                templateInput.value = targetType === 'certificate' ? 'certificate-award' : 'auto';
            });
        });

        form.addEventListener('submit', () => {
            templateInput.value = documentTypeSelect.value === 'certificate' ? 'certificate-award' : 'auto';
        });
    })();
</script>
@endpush
