<x-app-layout>
    <div class="space-y-6">
        <x-admin.page-header
            title="Import & Export"
            eyebrow="Data Management"
            description="Bulk import or export students and results from spreadsheets." />

        @if (session('status'))
            <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Import Students</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        Upload a CSV or Excel file. Passports remain manual uploads.
                    </p>
                </div>

                <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf

                    <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <p class="mt-3 text-sm font-medium text-slate-700">Drag &amp; drop your spreadsheet here, or</p>
                        <label class="btn-secondary mt-3 cursor-pointer">
                            Choose File
                            <input type="file" name="file" accept=".csv,.xlsx,.xls,.txt" class="sr-only">
                        </label>
                        <p class="mt-2 text-xs text-slate-400">CSV, XLSX or XLS &middot; maximum 5MB</p>
                        @error('file')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm">
                        <input type="checkbox" name="update_existing" value="1"
                            class="mt-0.5 rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                        <span>
                            <span class="font-semibold text-slate-700">Update existing students</span>
                            <span class="block text-xs text-slate-400">
                                If a row's Foundation Number already exists, its details will be updated instead of skipped.
                            </span>
                        </span>
                    </label>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.export', 'csv') }}" class="text-sm font-semibold text-secondary-700 hover:text-secondary-800">
                            Download sample template &rarr;
                        </a>
                        <button type="submit" class="btn-primary">Import Students</button>
                    </div>
                </form>

                <div class="border-t border-slate-100 px-6 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Required Columns</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['surname', 'first_name', 'middle_name', 'foundation_number', 'examination_number', 'subject_one', 'subject_two', 'subject_three'] as $column)
                            <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600">{{ $column }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Export Students</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        Export all records, or filter first and download as CSV or Excel.
                    </p>
                </div>

                <form action="{{ route('admin.export', 'xlsx') }}" method="GET" class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="status" class="label">Status</label>
                            <select id="status" name="status" class="input">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label for="subject" class="label">Subject</label>
                            <select id="subject" name="subject" class="input">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary flex-1">Export as Excel (.xlsx)</button>
                        <a href="{{ route('admin.export', ['csv', 'session' => request('session'), 'status' => request('status'), 'subject' => request('subject')]) }}"
                            class="btn-outline flex-1">Export as CSV</a>
                    </div>
                </form>

                <div class="border-t border-slate-100 px-6 py-4">
                    <p class="text-xs text-slate-400">
                        Exports include surname, names, foundation/JUPEB/examination numbers, subjects,
                        session, status, phone and email.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Import Results</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        Upload a CSV or Excel file with student grades. Subjects are matched from student records.
                    </p>
                </div>

                <form action="{{ route('admin.results.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf

                    <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <p class="mt-3 text-sm font-medium text-slate-700">Drag & drop your spreadsheet here, or</p>
                        <label class="btn-secondary mt-3 cursor-pointer">
                            Choose File
                            <input type="file" name="file" accept=".csv,.xlsx,.xls" class="sr-only">
                        </label>
                        <p class="mt-2 text-xs text-slate-400">CSV, XLSX or XLS &middot; maximum 5MB</p>
                        @error('file')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.sample-template', 'results') }}" class="text-sm font-semibold text-secondary-700 hover:text-secondary-800">
                            Download sample template &rarr;
                        </a>
                        <button type="submit" class="btn-primary">Import Results</button>
                    </div>
                </form>

                <div class="border-t border-slate-100 px-6 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Required Columns</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['foundation_number', 'grade_one', 'grade_two', 'grade_three'] as $column)
                            <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600">{{ $column }}</span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Grades must be A, B, C, D, E, F, X, Q, or W. Subjects are auto-matched from student records.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Export Results</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        Export all results, or filter first and download as CSV or Excel.
                    </p>
                </div>

                <form action="{{ route('admin.results.export', 'xlsx') }}" method="GET" class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="result_session" class="label">Session</label>
                            <select id="result_session" name="session" class="input">
                                <option value="">All Sessions</option>
                                @foreach (\App\Models\Result::distinct()->orderByDesc('session')->pluck('session') as $session)
                                    <option value="{{ $session }}">{{ $session }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="result_status" class="label">Status</label>
                            <select id="result_status" name="status" class="input">
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary flex-1">Export as Excel (.xlsx)</button>
                        <a href="{{ route('admin.results.export', ['csv', 'session' => request('session'), 'status' => request('status')]) }}"
                            class="btn-outline flex-1">Export as CSV</a>
                    </div>
                </form>

                <div class="border-t border-slate-100 px-6 py-4">
                    <p class="text-xs text-slate-400">
                        Exports include student details, subject names, grades, points, and result status.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Import Subjects</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        Upload a CSV or Excel file to add or update subjects.
                    </p>
                </div>

                <form action="{{ route('admin.subjects.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf

                    <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <p class="mt-3 text-sm font-medium text-slate-700">Drag & drop your spreadsheet here, or</p>
                        <label class="btn-secondary mt-3 cursor-pointer">
                            Choose File
                            <input type="file" name="file" accept=".csv,.xlsx,.xls" class="sr-only">
                        </label>
                        <p class="mt-2 text-xs text-slate-400">CSV, XLSX or XLS &middot; maximum 5MB</p>
                        @error('file')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.sample-template', 'subjects') }}" class="text-sm font-semibold text-secondary-700 hover:text-secondary-800">
                            Download sample template &rarr;
                        </a>
                        <button type="submit" class="btn-primary">Import Subjects</button>
                    </div>
                </form>

                <div class="border-t border-slate-100 px-6 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Required Columns</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['name', 'is_active'] as $column)
                            <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600">{{ $column }}</span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-400">is_active should be 1 (active) or 0 (inactive). Defaults to 1 if omitted.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
