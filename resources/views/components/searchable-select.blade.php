@props(['model', 'label' => null, 'options' => [], 'placeholder' => 'Search and select…', 'required' => false, 'hint' => null, 'selectedValue' => null])

@php
    $options = is_array($options) ? $options : collect($options)->toArray();
    $selected = $selectedValue;
@endphp

<div class="relative"
    x-data="{
        open: false,
        query: '',
        selected: @js($selected),
        options: @js(collect($options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->toArray()),
        get filtered() {
            if (! this.query) return this.options;
            return this.options.filter(o => o.label.toLowerCase().includes(this.query.toLowerCase()));
        },
        select(option) {
            this.selected = option.label;
            @this.set('{{ $model }}', option.value);
            this.open = false;
            this.query = '';
        },
        clear() {
            this.selected = null;
            @this.set('{{ $model }}', null);
        },
    }"
    x-on:keydown.escape="open = false"
    x-on:click.outside="open = false">

    @if ($label)
        <label class="label">{{ $label }} @if ($required)<span class="text-red-500">*</span>@endif</label>
    @endif

    <button type="button" x-on:click="open = !open"
        class="flex w-full items-center justify-between gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
        <span x-text="selected ?? '{{ $placeholder }}'" x-bind:class="selected ? 'text-slate-800' : 'text-slate-400'">{{ $placeholder }}</span>
        <span class="flex items-center gap-2">
            <template x-if="selected">
                <span x-on:click.stop="clear()" class="rounded p-0.5 text-slate-400 hover:text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>
            </template>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </span>
    </button>

    <div x-show="open" x-transition x-cloak
        class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
        <div class="border-b border-slate-100 p-2">
            <input type="text" x-model="query" placeholder="Type to search…"
                class="w-full rounded-md border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
        </div>
        <ul class="max-h-56 overflow-y-auto py-1">
            <template x-for="option in filtered" :key="option.value">
                <li>
                    <button type="button" x-on:click="select(option)"
                        class="flex w-full items-center justify-between px-3 py-2 text-sm text-slate-700 hover:bg-primary-50">
                        <span x-text="option.label"></span>
                        <svg x-show="selected === option.label" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 text-primary-700"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd"/></svg>
                    </button>
                </li>
            </template>
            <li x-show="! filtered.length" class="px-3 py-2 text-sm text-slate-400">No options match your search.</li>
        </ul>
    </div>

    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
