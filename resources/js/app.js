import './bootstrap';

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
    init() {
        if (count > 1) {
            setInterval(() => this.next(), interval);
        }
    },
    next() {
        this.index = (this.index + 1) % this.count;
    },
    prev() {
        this.index = (this.index - 1 + this.count) % this.count;
    },
}));

Alpine.start();
