@props([
    'name' => null,
    'accept' => '.csv,.xlsx,.xls',
    'hint' => 'CSV, XLSX or XLS &middot; maximum 5MB',
    'wireModel' => null,
])

<div
    x-data="importDropzone()"
    class="rounded-xl border-2 border-dashed p-8 text-center transition"
    :class="dragging ? 'border-primary-500 bg-primary-50' : (fileName ? 'border-primary-300 bg-primary-50/50' : 'border-slate-300 bg-slate-50 hover:border-primary-400')"
    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="onDrop"
>
    <input
        type="file"
        @if ($wireModel)
            wire:model="{{ $wireModel }}"
        @else
            name="{{ $name }}"
        @endif
        accept="{{ $accept }}"
        class="sr-only"
        x-ref="input"
        x-on:change="onChange"
    >

    <div x-show="!fileName">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
        <p class="mt-3 text-sm font-medium text-slate-700">Drag &amp; drop your spreadsheet here, or</p>
        <button type="button" class="btn-secondary mt-3" x-on:click="$refs.input.click()">Choose File</button>
        <p class="mt-2 text-xs text-slate-400">{!! $hint !!}</p>
    </div>

    <div x-show="fileName" x-cloak class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3 3m0 0l-3-3m3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </span>
        <span class="min-w-0 text-center sm:text-left">
            <span class="block max-w-xs truncate text-sm font-semibold text-slate-800" x-text="fileName"></span>
            <span class="block text-xs text-slate-400" x-text="fileSize"></span>
        </span>
        <span class="flex items-center gap-2">
            <button type="button" class="btn-secondary !px-3 !py-1.5 text-xs" x-on:click="$refs.input.click()">Change</button>
            <button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Remove file" x-on:click="clearFile">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </span>
    </div>
</div>
