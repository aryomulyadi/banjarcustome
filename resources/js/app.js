import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('theme', () => ({
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },
}));

Alpine.data('heroSlider', (count, interval = 5000) => ({
    index: 0,
    count,
    paused: false,
    tx: 0,
    ty: 0,
    init() {
        if (count < 2) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.paused = true;

            return;
        }

        setInterval(() => {
            if (!this.paused) {
                this.next();
            }
        }, interval);
    },
    toggle() {
        this.paused = !this.paused;
    },
    next() {
        this.index = (this.index + 1) % this.count;
    },
    prev() {
        this.index = (this.index - 1 + this.count) % this.count;
    },
    swipeStart(e) {
        const t = e.touches[0];
        this.tx = t.clientX;
        this.ty = t.clientY;
    },
    swipeEnd(e) {
        const t = e.changedTouches[0];
        const dx = t.clientX - this.tx;
        const dy = t.clientY - this.ty;

        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
            if (dx < 0) {
                this.next();
            } else {
                this.prev();
            }
        }
    },
}));

Alpine.data('imageSlider', (count) => ({
    index: 0,
    tx: 0,
    ty: 0,
    swipeStart(e) {
        const t = e.touches[0];
        this.tx = t.clientX;
        this.ty = t.clientY;
    },
    swipeEnd(e) {
        const t = e.changedTouches[0];
        const dx = t.clientX - this.tx;
        const dy = t.clientY - this.ty;

        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
            this.index = dx < 0 ? (this.index + 1) % count : (this.index - 1 + count) % count;
        }
    },
}));

Alpine.data('galleryLightbox', () => ({
    items: [],
    index: 0,
    init() {
        this.items = [...this.$root.querySelectorAll('a[data-lb]')].map((a) => ({
            url: a.href,
            title: a.dataset.title || '',
        }));
    },
    open(i) {
        if (!this.items.length) {
            return;
        }

        this.index = i;
        this.$refs.dialog.showModal();
    },
    close() {
        this.$refs.dialog.close();
    },
    next() {
        this.index = (this.index + 1) % this.items.length;
    },
    prev() {
        this.index = (this.index - 1 + this.items.length) % this.items.length;
    },
    get current() {
        return this.items[this.index] || null;
    },
}));

Alpine.start();
