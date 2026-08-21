import './bootstrap';

window.formatFileSize = function (bytes) {
    if (! bytes && bytes !== 0) return '';

    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unit = 0;

    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit++;
    }

    return `${size % 1 === 0 ? size : size.toFixed(1)} ${units[unit]}`;
};

window.importDropzone = function () {
    return {
        dragging: false,
        fileName: '',
        fileSize: '',

        onChange() {
            const file = this.$refs.input?.files?.[0];

            this.fileName = file ? file.name : '';
            this.fileSize = file ? formatFileSize(file.size) : '';
            this.dragging = false;
        },

        onDrop(event) {
            this.dragging = false;

            const files = event.dataTransfer?.files;

            if (!files || files.length === 0) return;

            try {
                const transfer = new DataTransfer();

                transfer.items.add(files[0]);
                this.$refs.input.files = transfer.files;
            } catch {
                this.$refs.input.files = files;
            }

            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        clearFile() {
            this.$refs.input.value = '';
            this.fileName = '';
            this.fileSize = '';
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
    };
};
