// ScrollToTopButton.js
export class ScrollToTopButton {
    #button;
    #threshold = 300; // показывать после прокрутки на 300px

    constructor() {
        this.#button = document.createElement('button');
        this.#button.className = 'scroll-to-top';
        // this.#button.classList.add('bg-secondary');
        this.#button.innerHTML = '↑';
        this.#button.setAttribute('aria-label', 'Прокрутить наверх');
        document.body.appendChild(this.#button);

        this.#button.addEventListener('click', () => this.#scrollToTop());
        window.addEventListener('scroll', () => this.#onScroll(), { passive: true });
    }

    #onScroll() {
        if (window.scrollY > this.#threshold) {
            this.#button.classList.add('is-visible');
        } else {
            this.#button.classList.remove('is-visible');
        }
    }

    #scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}