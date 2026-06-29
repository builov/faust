// Loader.js
export class Loader {
    #element;

    /**
     * @param {HTMLElement|null} element - если передан, используется он; иначе создаётся оверлей
     */
    constructor(element = null) {
        if (element) {
            this.#element = element;
        } else {
            this.#element = this.#createOverlay();
            document.body.appendChild(this.#element);
        }
    }

    #createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'loader-overlay d-none';
        const spinner = document.createElement('div');
        spinner.className = 'loader-spinner';
        overlay.appendChild(spinner);
        return overlay;
    }

    show() {
        this.#element?.classList.remove('d-none'); // или убрать скрывающий класс
        // можно добавить aria-busy="true" и т.п.
        console.log('загрузка...');
    }

    hide() {
        this.#element?.classList.add('d-none');
        console.log('загружено');
    }
}