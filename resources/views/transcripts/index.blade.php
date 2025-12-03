@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter transcripts</span>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('transcripts.import') }}">Import new file</a>
    </div>
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('transcripts.index') }}">
            <div class="col-md-3">
                <label class="form-label">Search name, serial, national or student id</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="e.g. John or 1234">
            </div>
            <div class="col-md-3">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select">
                    <option value="">Any</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($filters['course_id'] === $course->id)>{{ $course->name }} ({{ $course->shortcode }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Batch</label>
                <input type="text" name="batch" class="form-control" value="{{ $filters['batch'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Program</label>
                <input type="text" name="program" class="form-control" value="{{ $filters['program'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Level</label>
                <input type="text" name="level" class="form-control" value="{{ $filters['level'] }}">
            </div>
            <div class="col-12">
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
                <div class="row mb-3 align-items-end g-2">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label">Bulk document type</label>
                        <div class="d-flex gap-2 align-items-center">
                            <select name="document_type" id="document-type" class="form-select">
                                <option value="transcript" selected>Transcript</option>
                                <option value="certificate">Certificate</option>
                            </select>
                            <button type="submit" class="btn btn-success flex-shrink-0" id="bulk-submit">Download Selected</button>
                        </div>
                        <div class="form-text">Choose whether selected rows download as transcripts or certificates.</div>
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <label class="form-label">Transcript template</label>
                        <select name="template" class="form-select" id="template-select">
                            <option value="default">Default</option>
                            <option value="compact">Compact</option>
                            <option value="bachelors-single">Bachelors - Single</option>
                            <option value="certificate-transcript">Certificate Course Transcript</option>
                            <option value="certificate-award" class="d-none">Certificate Award</option>
                        </select>
                    </div>
                    <div class="col-md-5 col-lg-6">
                        <p class="mb-1 text-muted">Select one or more rows, choose the document type and template (for transcripts), then click Download.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Batch</th>
                                <th>CGPA</th>
                                <th>Completed</th>
                                <th>Modules</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transcripts as $transcript)
                                <tr>
                                    <td><input class="form-check-input transcript-checkbox" type="checkbox" name="transcript_ids[]" value="{{ $transcript->id }}"></td>
                                    <td>
                                        <div class="fw-semibold">{{ $transcript->student->name }}</div>
                                        <div class="small text-muted">ID: {{ $transcript->student->student_identifier ?? 'n/a' }} | Serial: {{ $transcript->student->certificate_serial_number ?? 'n/a' }}</div>
                                    </td>
                                    <td>{{ $transcript->course->name }} ({{ $transcript->course->shortcode }})</td>
                                    <td>{{ $transcript->student->batch_no ?? 'n/a' }}</td>
                                    <td>{{ $transcript->cgpa ?? 'n/a' }}</td>
                                    <td>{{ $transcript->completed_date?->format('Y-m-d') ?? 'n/a' }}</td>
                                    <td>{{ $transcript->moduleResults->count() }}</td>
                                    <td class="d-flex gap-2">
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
        const templateSelect = document.getElementById('template-select');
        const documentTypeSelect = document.getElementById('document-type');
        const hiddenCertificateValue = 'certificate-award';

        const ensureTemplateMatchesType = (docType) => {
            if (docType === 'certificate') {
                templateSelect.value = hiddenCertificateValue;
                [...templateSelect.options].forEach((opt) => {
                    opt.classList.toggle('d-none', opt.value !== hiddenCertificateValue);
                });
            } else {
                if (templateSelect.value === hiddenCertificateValue) {
                    templateSelect.value = 'default';
                }
                [...templateSelect.options].forEach((opt) => {
                    opt.classList.toggle('d-none', opt.value === hiddenCertificateValue);
                });
            }
        };

        documentTypeSelect.addEventListener('change', (event) => {
            ensureTemplateMatchesType(event.target.value);
        });

        document.querySelectorAll('.document-trigger').forEach((button) => {
            button.addEventListener('click', () => {
                const targetType = button.dataset.documentType || 'transcript';
                documentTypeSelect.value = targetType;
                ensureTemplateMatchesType(targetType);
            });
        });

        form.addEventListener('submit', () => {
            ensureTemplateMatchesType(documentTypeSelect.value);
        });
    })();
</script>
@endpush
