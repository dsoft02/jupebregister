<div x-data="{
    init() {
        Alpine.store('confirmModal', {
            open: false,
            title: '',
            message: '',
            confirmText: 'Confirm',
            _onConfirm: null,
            show(options) {
                this.title = options.title || 'Are you sure?';
                this.message = options.message || '';
                this.confirmText = options.confirmText || 'Confirm';
                this._onConfirm = options.onConfirm || null;
                this.open = true;
            },
            confirm() {
                this._onConfirm?.();
                this.open = false;
            },
            cancel() {
                this.open = false;
            }
        });
    }
}" x-show="$store.confirmModal.open" x-cloak
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="$store.confirmModal.cancel()"></div>

    <!-- Modal card -->
    <div x-show="$store.confirmModal.open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900" x-text="$store.confirmModal.title"></h3>
        <p class="mt-2 text-sm text-slate-500" x-text="$store.confirmModal.message"></p>
        <div class="mt-6 flex items-center justify-end gap-3">
            <button x-on:click="$store.confirmModal.cancel()" type="button"
                class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                Cancel
            </button>
            <button x-on:click="$store.confirmModal.confirm()" type="button"
                class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-red-600 transition hover:bg-red-700">
                <span x-text="$store.confirmModal.confirmText"></span>
            </button>
        </div>
    </div>
</div>
