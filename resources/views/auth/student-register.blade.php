<x-public-layout>
    <x-slot name="title">Student Registration</x-slot>

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-primary-800">Student Registration</h1>
            <p class="mt-2 text-sm text-slate-500">
                Register your details for the PAAU Foundation School JUPEB programme.
                Your registration will be reviewed by the Programme Officer.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="mb-2 text-sm font-semibold text-red-800">Please fix the following errors:</p>
                <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" class="card space-y-8 p-6 sm:p-8">
            @csrf

            <div>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">
                    Personal Information
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="surname" class="label">Surname <span class="text-red-500">*</span></label>
                        <input type="text" id="surname" name="surname" value="{{ old('surname') }}" class="input" required>
                    </div>
                    <div>
                        <label for="first_name" class="label">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" class="input" required>
                    </div>
                    <div>
                        <label for="middle_name" class="label">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="input">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">
                    Registration Numbers
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="foundation_number" class="label">Foundation Number <span class="text-red-500">*</span></label>
                        <input type="text" id="foundation_number" name="foundation_number" value="{{ old('foundation_number') }}" class="input" placeholder="e.g. PAAU/FS/001" required>
                    </div>
                    <div>
                        <label for="jupeb_number" class="label">JUPEB Number</label>
                        <input type="text" id="jupeb_number" name="jupeb_number" value="{{ old('jupeb_number') }}" class="input" placeholder="e.g. 23J/1234">
                    </div>
                    <div>
                        <label for="examination_number" class="label">Examination Number</label>
                        <input type="text" id="examination_number" name="examination_number" value="{{ old('examination_number') }}" class="input" placeholder="Examination number">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">
                    Subjects &amp; Contact
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="subject_one_id" class="label">Subject 1 <span class="text-red-500">*</span></label>
                        <select id="subject_one_id" name="subject_one_id" class="input" required>
                            <option value="">Select first subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_one_id') == $subject->id)>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="subject_two_id" class="label">Subject 2 <span class="text-red-500">*</span></label>
                        <select id="subject_two_id" name="subject_two_id" class="input" required>
                            <option value="">Select second subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_two_id') == $subject->id)>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="subject_three_id" class="label">Subject 3 <span class="text-red-500">*</span></label>
                        <select id="subject_three_id" name="subject_three_id" class="input" required>
                            <option value="">Select third subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_three_id') == $subject->id)">
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="phone" class="label">Phone <span class="text-red-500">*</span></label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="input" placeholder="+234 800 000 0000" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email" class="label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="input" placeholder="student@example.com">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">
                    Passport Photo
                </h2>
                <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400" id="passport-dropzone">
                    <div id="passport-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <p class="mt-3 text-sm font-medium text-slate-700">Drag &amp; drop a passport photo here, or</p>
                        <label class="btn-secondary mt-3 cursor-pointer">
                            Browse Files
                            <input type="file" id="passport" name="passport" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        <p class="mt-2 text-xs text-slate-400">JPG, PNG or WebP &middot; maximum 500KB</p>
                    </div>
                    <div id="passport-preview" class="hidden">
                        <img id="passport-img" src="" alt="Passport preview" class="mx-auto h-36 w-36 rounded-full object-cover ring-4 ring-white shadow-md">
                        <p id="passport-name" class="mt-3 text-sm font-medium text-slate-700"></p>
                        <p id="passport-size" class="text-xs text-slate-400"></p>
                        <button type="button" id="passport-remove" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Remove
                        </button>
                    </div>
                    <p id="passport-error" class="hidden mt-2 text-xs font-medium text-red-600"></p>
                </div>
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400">
                    Your submission will be marked <strong>Pending</strong> until reviewed by the Programme Officer.
                </p>
                <button type="submit" class="btn-primary px-8 py-3 text-base">
                    Submit Registration
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input    = document.getElementById('passport');
            const dropzone = document.getElementById('passport-dropzone');
            const empty    = document.getElementById('passport-empty');
            const preview  = document.getElementById('passport-preview');
            const img      = document.getElementById('passport-img');
            const nameEl   = document.getElementById('passport-name');
            const sizeEl   = document.getElementById('passport-size');
            const errorEl  = document.getElementById('passport-error');
            const removeBtn = document.getElementById('passport-remove');
            const MAX_BYTES = 500 * 1024;

            function formatSize(bytes) {
                return bytes < 1024 ? bytes + ' B'
                     : bytes < 1048576 ? (bytes / 1024).toFixed(1) + ' KB'
                     : (bytes / 1048576).toFixed(1) + ' MB';
            }

            function showPreview(file) {
                errorEl.classList.add('hidden');

                if (file.size > MAX_BYTES) {
                    errorEl.textContent = 'File is ' + formatSize(file.size) + '. Maximum allowed is 500KB.';
                    errorEl.classList.remove('hidden');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                    nameEl.textContent = file.name;
                    sizeEl.textContent = formatSize(file.size);
                    empty.classList.add('hidden');
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }

            input.addEventListener('change', (e) => {
                if (e.target.files.length) showPreview(e.target.files[0]);
            });

            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('border-primary-400', 'bg-primary-50'); });
            dropzone.addEventListener('dragleave', () => { dropzone.classList.remove('border-primary-400', 'bg-primary-50'); });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-primary-400', 'bg-primary-50');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });

            removeBtn.addEventListener('click', () => {
                input.value = '';
                img.src = '';
                preview.classList.add('hidden');
                empty.classList.remove('hidden');
            });
        });
    </script>
</x-public-layout>
