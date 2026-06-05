// ContextMenu.js
export class ContextMenu {
    #element = null;
    #items = [];
    #x = 0;
    #y = 0;
    #onHide = null;

    /**
     * @param {Array<{label: string, action: function}>} items
     * @param {number} x
     * @param {number} y
     * @param {function} [onHide] — вызывается при закрытии меню
     */
    constructor({ items, x, y, onHide }) {
        this.#items = items;
        this.#x = x;
        this.#y = y;
        this.#onHide = onHide;
    }

    show() {
        // Закрываем предыдущее меню, если есть
        ContextMenu.#current?.hide();

        this.#element = document.createElement('ul');
        this.#element.className = 'context-menu';
        this.#element.style.left = `${this.#x}px`;
        this.#element.style.top = `${this.#y}px`;

        this.#items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.label;
            li.addEventListener('click', (e) => {
                e.stopPropagation();
                item.action?.();
                this.hide();
            });
            this.#element.appendChild(li);
        });

        document.body.appendChild(this.#element);
        ContextMenu.#current = this;

        // Закрытие при клике вне
        setTimeout(() => {
            document.addEventListener('click', this.#handleOutsideClick, { capture: true });
            document.addEventListener('contextmenu', this.#handleOutsideClick, { capture: true });
        }, 0);
    }

    hide() {
        if (this.#element) {
            this.#element.remove();
            this.#element = null;
        }
        document.removeEventListener('click', this.#handleOutsideClick, { capture: true });
        document.removeEventListener('contextmenu', this.#handleOutsideClick, { capture: true });

        if (ContextMenu.#current === this) {
            ContextMenu.#current = null;
        }
        this.#onHide?.();
    }

    #handleOutsideClick = (e) => {
        if (this.#element && !this.#element.contains(e.target)) {
            this.hide();
        }
    }

    static #current = null;
}