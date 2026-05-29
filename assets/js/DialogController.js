export class DialogController {
    constructor() {
        this._initGlobalClose();
    }

    init(dialogElements) {
        dialogElements.forEach(dialog => this.#setupDialog(dialog));
    }

    #setupDialog(dialog) {
        const trigger = document.createElement('button');
        trigger.className = 'comment-trigger';
        trigger.textContent = '*';
        dialog.before(trigger);

        const removeCloseButton = () => {
            const closeBtn = dialog.querySelector('.dialog-close');
            if (closeBtn) closeBtn.remove();
        };

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            // Закрыть все остальные открытые диалоги
            document.querySelectorAll('dialog[open]').forEach(d => {
                if (d !== dialog) d.close();
            });

            if (!dialog.open) {
                this.#openWithCloseButton(dialog);
            } else {
                dialog.close();
            }
        });

        dialog.addEventListener('close', removeCloseButton);
        dialog.addEventListener('click', (e) => e.stopPropagation());
    }

    #openWithCloseButton(dialog) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'dialog-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Закрыть');
        dialog.prepend(closeBtn);
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dialog.close();
        });

        dialog.show();
        setTimeout(() => {
            dialog.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        }, 50);
    }

    _initGlobalClose() {
        document.addEventListener('click', () => {
            document.querySelectorAll('dialog[open]').forEach(d => d.close());
        });
    }
}