@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'disabled' => $disabled, 'class' => 'inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-700/20 transition-all duration-200 hover:bg-primary-800 hover:shadow-lg hover:shadow-primary-700/25 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
