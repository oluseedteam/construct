import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Animated statistic counter: counts up once when scrolled into view. */
Alpine.data('counter', (target = 0) => ({
    value: 0,
    target: Number(target),
    started: false,
    start() {
        if (this.started) return;
        this.started = true;
        if (reduceMotion || this.target === 0) {
            this.value = this.target;
            return;
        }
        const duration = 1800;
        const t0 = performance.now();
        const tick = (now) => {
            const p = Math.min((now - t0) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            this.value = Math.round(this.target * eased);
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    },
    get display() {
        return this.value.toLocaleString();
    },
}));

/* Testimonial carousel */
Alpine.data('carousel', (count = 1) => ({
    index: 0,
    count: Number(count),
    timer: null,
    init() {
        if (this.count > 1 && !reduceMotion) this.play();
    },
    play() {
        this.stop();
        this.timer = setInterval(() => this.next(), 7000);
    },
    stop() {
        clearInterval(this.timer);
    },
    next() {
        this.index = (this.index + 1) % this.count;
    },
    prev() {
        this.index = (this.index - 1 + this.count) % this.count;
    },
}));

/*
 * Image lightbox. Triggers are any element with [data-lightbox] (+ data-src / data-title);
 * the list is read from the DOM when opened, so it keeps working after Livewire filters swap the grid.
 */
Alpine.data('lightbox', () => ({
    nodes: [],
    current: null,
    open(el) {
        this.nodes = [...document.querySelectorAll('[data-lightbox]')];
        this.current = Math.max(0, this.nodes.indexOf(el));
        document.documentElement.classList.add('overflow-hidden');
    },
    close() {
        this.current = null;
        document.documentElement.classList.remove('overflow-hidden');
    },
    next() {
        this.current = (this.current + 1) % this.nodes.length;
    },
    prev() {
        this.current = (this.current - 1 + this.nodes.length) % this.nodes.length;
    },
    get item() {
        return this.current === null || !this.nodes[this.current] ? {} : this.nodes[this.current].dataset;
    },
}));

/* Scroll reveal for [data-reveal] elements (re-run after Livewire DOM updates). */
function initReveal(root = document) {
    const nodes = root.querySelectorAll('[data-reveal]:not(.is-visible)');
    if (!nodes.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
        nodes.forEach((n) => n.classList.add('is-visible'));
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );
    nodes.forEach((n) => io.observe(n));
}

document.addEventListener('DOMContentLoaded', () => initReveal());
document.addEventListener('livewire:navigated', () => initReveal());
document.addEventListener('livewire:init', () => {
    Livewire.hook('morphed', () => requestAnimationFrame(() => initReveal()));
});

Livewire.start();
