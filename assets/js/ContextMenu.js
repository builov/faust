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
        this.#onHide = onHide;
    }

    /**
     * @param {number} x
     * @param {number} y
     */
    show(x, y) {
        // Закрываем предыдущее меню, если есть
        ContextMenu.#current?.hide();

        this.#x = x;
        this.#y = y;

        this.#element = document.createElement('ul');
        this.#element.className = 'context-menu';

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

        // 1. Сначала добавляем элемент в body, чтобы браузер смог рассчитать его реальные размеры
        document.body.appendChild(this.#element);

        // 2. Получаем размеры самого меню (ширину и высоту)
        const menuWidth = this.#element.offsetWidth;
        const menuHeight = this.#element.offsetHeight;

        // 3. Получаем размеры видимой области экрана (окна браузера)
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        // 4. Проверяем правый край: если меню выходит за рамки, сдвигаем его влево на свою ширину
        let finalX = x;
        if (x + menuWidth > windowWidth) {
            finalX = x - menuWidth;
            // Защита на случай, если экран смартфона слишком узкий (меньше ширины меню)
            if (finalX < 0) finalX = 0;
        }

        // 5. Проверяем нижний край относительно видимого окна (y — это координата относительно вьюпорта)
        let finalY = y;
        if (y + menuHeight > windowHeight) {
            finalY = y - menuHeight;
            // Защита на случай, если меню длиннее, чем высота экрана
            if (finalY < 0) finalY = 0;
        }

        // 6. Применяем финальные скорректированные координаты
        this.#element.style.left = `${finalX}px`;
        this.#element.style.top = `${finalY}px`;

        ContextMenu.#current = this;

        // Закрытие при клике вне
        setTimeout(() => {
            document.addEventListener('click', this.#handleOutsideClick, { capture: true });
            document.addEventListener('contextmenu', this.#handleOutsideClick, { capture: true });
            // Добавляем touchstart для быстрой обработки тапа мимо меню на смартфонах
            document.addEventListener('touchstart', this.#handleOutsideClick, { capture: true });
        }, 0);
    }

    hide() {
        if (this.#element) {
            this.#element.remove();
            this.#element = null;
        }
        document.removeEventListener('click', this.#handleOutsideClick, { capture: true });
        document.removeEventListener('contextmenu', this.#handleOutsideClick, { capture: true });
        document.removeEventListener('touchstart', this.#handleOutsideClick, { capture: true });

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