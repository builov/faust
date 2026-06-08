export class ButtonToggleManager {
    #tableManager;

    constructor(tableManager) {
        this.#tableManager = tableManager;
    }

    syncButtonState(button, textId) {
        const isActive = this.#tableManager.hasColumn(textId);
        if (isActive) {
            button.classList.replace('btn-outline-secondary', 'btn-secondary');
        } else {
            button.classList.replace('btn-secondary', 'btn-outline-secondary');
        }
    }
}