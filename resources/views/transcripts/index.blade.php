@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3 px-3">
        <span class="fw-semibold">Filter transcripts</span>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('transcripts.import') }}">Import new file</a>
    </div>
    <div class="card-body pt-3">
        <form class="row g-3 align-items-end" method="GET" action="{{ route('transcripts.index') }}">
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
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-uppercase text-muted">Rows per page</label>
                <select name="per_page" class="form-select">
                    @foreach(['10', '25', '50', '100', 'all'] as $option)
                        <option value="{{ $option }}" @selected($filters['per_page'] === (string) $option)>
                            {{ $option === 'all' ? 'All' : $option }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2 justify-content-start">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline-secondary" href="{{ route('transcripts.index') }}">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header py-3 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Transcripts</span>
        <span class="text-muted small">Select rows, choose document type, then download.</span>
    </div>
    <div class="card-body pt-3">
        @if($transcripts->isEmpty())
            <p class="text-muted mb-0">No transcripts found. Import a CSV to get started.</p>
        @else
            <form method="POST" action="{{ route('transcripts.pdf') }}" id="transcript-form">
                @csrf
                <input type="hidden" name="template" id="template-input" value="auto">
                <div class="row mb-3 align-items-end g-3">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label small text-uppercase text-muted mb-1">Bulk document type</label>
                        <div class="d-flex gap-2">
                            <select name="document_type" id="document-type" class="form-select">
                                <option value="transcript" selected>Transcript</option>
                                <option value="certificate">Certificate</option>
                            </select>
                            <button type="submit" class="btn btn-success" id="bulk-submit">Download Selected</button>
                        </div>
                        <div class="form-text">Transcript templates are chosen automatically by course level.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light align-middle">
                            <tr>
                                <th class="text-center py-3" style="width:50px;"><input type="checkbox" id="select-all"></th>
                                <th class="py-3 px-3">Student</th>
                                <th class="py-3 px-3">Course</th>
                                <th class="text-center py-3 px-2">Batch</th>
                                <th class="text-center py-3 px-2">CGPA</th>
                                <th class="text-center py-3 px-2">Completed</th>
                                <th class="text-center py-3 px-2">Modules</th>
                                <th class="text-center py-3 px-3" style="width:240px;">Actions</th>
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
                                                type="button"
                                                class="btn btn-sm btn-outline-primary document-trigger"
                                                data-document-type="transcript"
                                                data-template="auto"
                                                data-transcript-id="{{ $transcript->id }}"
                                            >
                                                Download Transcript
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary document-trigger"
                                                data-document-type="certificate"
                                                data-template="certificate-award"
                                                data-transcript-id="{{ $transcript->id }}"
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
                {{ $transcripts->links('pagination::bootstrap-5') }}
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
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const targetType = button.dataset.documentType || 'transcript';
                const template = button.dataset.template || (targetType === 'certificate' ? 'certificate-award' : 'auto');
                const targetId = button.dataset.transcriptId;

                documentTypeSelect.value = targetType;
                templateInput.value = template;

                document.querySelectorAll('.transcript-checkbox').forEach((checkbox) => {
                    checkbox.checked = checkbox.value === targetId;
                });

                form.requestSubmit();
            });
        });

        form.addEventListener('submit', () => {
            templateInput.value = documentTypeSelect.value === 'certificate' ? 'certificate-award' : 'auto';
        });
    })();
</script>
@endpush
