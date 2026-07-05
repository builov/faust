export class ToastNotification {
    static #container = null;

    /**
     * Создает контейнер для тостов, если он еще не существует
     */
    static #getContainer() {
        if (!this.#container) {
            this.#container = document.createElement('div');
            this.#container.className = 'toast-container';
            document.body.appendChild(this.#container);
        }
        return this.#container;
    }

    /**
     * Показывает всплывающее уведомление
     * @param {string} message - Текст сообщения
     * @param {number} duration - Время показа в мс (0 — не закрывать автоматически)
     */
    static show(message, duration = 4000) {
        const container = this.#getContainer();

        const toast = document.createElement('div');
        toast.className = 'toast-item';

        toast.innerHTML = `
            <div class="toast-content">${message}</div>
            <button class="toast-close-btn" aria-label="Закрыть">&times;</button>
            ${duration > 0 ? `<div class="toast-progress" style="animation-duration: ${duration}ms"></div>` : ''}
        `;

        container.appendChild(toast);

        let autoCloseTimeout;

        // Функция плавного удаления
        const closeToast = () => {
            clearTimeout(autoCloseTimeout);
            toast.classList.add('toast-hiding');
            // Ждем окончания CSS-анимации исчезновения перед удалением из DOM
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        };

        // Закрытие по клику на крестик
        toast.querySelector('.toast-close-btn').addEventListener('click', closeToast);

        // Автозакрытие
        if (duration > 0) {
            autoCloseTimeout = setTimeout(closeToast, duration);
        }
    }
}
