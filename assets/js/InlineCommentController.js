export class InlineCommentController {
    constructor() {
        this.#initGlobalListeners();
    }

    /** Создает триггеры "*" перед диалогами. Безопасно вызывать повторно для новых элементов из XHR. */
    init(commentElements) {
        commentElements.forEach(comment => this.#setupCommentTrigger(comment));
    }

    /** Создает кнопку "*" перед диалогом, если её там ещё нет */
    #setupCommentTrigger(comment) {
        // Защита от дублирования: проверяем, не создана ли кнопка ранее
        const prevElement = comment.previousElementSibling;
        if (prevElement && prevElement.classList.contains('comment-trigger')) {
            return;
        }

        const trigger = document.createElement('button');
        trigger.className = 'comment-trigger';
        trigger.textContent = '*';
        comment.before(trigger);
    }

    #initGlobalListeners() {
        // Делегирование кликов на весь документ
        document.addEventListener('click', (e) => {
            const target = e.target;

            // 1. Клик по кнопке-триггеру "*"
            const trigger = target.closest('.comment-trigger');
            if (trigger) {
                e.stopPropagation();
                const comment = trigger.nextElementSibling;
                if (comment && comment.tagName === 'DIALOG') {
                    this.#toggleComment(comment);
                }
                return;
            }

            // 2. Клик по кнопке закрытия "×" внутри диалога
            const closeBtn = target.closest('.inline-comment-close');
            if (closeBtn) {
                e.stopPropagation();
                const comment = closeBtn.closest('dialog');
                if (comment) comment.close();
                return;
            }

            // 3. Клик внутри тела самого диалога (чтобы он не закрывался при клике на свой контент)
            const insideComment = target.closest('dialog');
            if (insideComment) {
                e.stopPropagation();
                return;
            }

            // 4. Клик мимо всего — закрываем все открытые диалоги
            this.#closeAllComments();
        });

        // Глобальный перехват события закрытия (capture: true, т.к. событие 'close' не всплывает)
        document.addEventListener('close', (e) => {
            if (e.target.tagName === 'DIALOG') {
                this.#removeCloseButton(e.target);
            }
        }, true);
    }

    #toggleComment(comment) {
        // Закрыть все остальные открытые диалоги
        document.querySelectorAll('dialog[open]').forEach(d => {
            if (d !== comment) d.close();
        });

        if (!comment.open) {
            this.#openWithCloseButton(comment);
        } else {
            comment.close();
        }
    }

    #openWithCloseButton(comment) {
        // Защита от дублирования крестика
        if (!comment.querySelector('.inline-comment-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'inline-comment-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Закрыть');
            comment.prepend(closeBtn);
        }

        comment.show();
        setTimeout(() => {
            comment.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        }, 50);
    }

    #removeCloseButton(comment) {
        const closeBtn = comment.querySelector('.inline-comment-close');
        if (closeBtn) closeBtn.remove();
    }

    #closeAllComments() {
        document.querySelectorAll('dialog[open]').forEach(d => d.close());
    }
}