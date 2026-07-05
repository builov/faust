export class NotificationModal {
    /**
     * Показывает модальное окно с таймером автозакрытия
     * @param {string} message - Текст уведомления
     * @param {string} title - Заголовок окна
     * @param {number} duration - Время отображения в миллисекундах (0 — не закрывать автоматически)
     */
    static show(message, title = 'Уведомление', duration = 5000) {

        console.log('show');

        const dialog = document.createElement('dialog');
        dialog.className = 'notification-dialog';

        dialog.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">${title}</span>
                <button class="notification-close-x" aria-label="Закрыть">&times;</button>
            </div>
            <div class="notification-body">
                <p>${message}</p>
            </div>
            ${duration > 0 ? `<div class="notification-progress-bar" style="animation-duration: ${duration}ms"></div>` : ''}
            <div class="notification-footer">
                <button class="notification-btn-ok">Ок</button>
            </div>
        `;

        document.body.appendChild(dialog);
        dialog.showModal();

        let autoCloseTimeout;

        // Функция закрытия с очисткой таймера
        const closeBox = () => {
            clearTimeout(autoCloseTimeout);
            dialog.close();
        };

        // Навешиваем события на кнопки закрытия
        dialog.querySelector('.notification-close-x').addEventListener('click', closeBox);
        dialog.querySelector('.notification-btn-ok').addEventListener('click', closeBox);

        // Автоматическое закрытие по таймеру
        if (duration > 0) {
            autoCloseTimeout = setTimeout(closeBox, duration);
        }

        // Удаление из DOM после закрытия
        dialog.addEventListener('close', () => dialog.remove());
    }
}
