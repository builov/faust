export class Fetcher {
    async getJson(url) {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
        return response.json();
    }

    async postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
        return response.json();
    }

    /**
     * Получить переводы для группы строк (или одной строки)
     * @param {string[]} lines - массив идентификаторов строк (data-num)
     * @param {string} type - 'line' | 'strophe' | 'replica'
     * @returns {Promise<Array<{label: string, texts: Array<{rowId: string, text: string}>}>>}
     */
    async fetchTranslations(lines, type) {
        return this.postJson('/', { lines, type });
    }

    // async fetchTranslations(rowId, type) {
    //     const url = `/translation.php?line=${encodeURIComponent(rowId)}&type=${encodeURIComponent(type)}`;
    //     return this.getJson(url);
    // }

    /** заглушка */
    // async fetchTranslations(rowId, type) {
    //     // Пример заглушки (заменить на реальный запрос)
    //     return new Promise(resolve => {
    //         setTimeout(() => {
    //             resolve([
    //                 { label: 'Перевод 1 (строка)', text: '<span class="trans">Переведённая строка 1</span>' },
    //                 { label: 'Перевод 2 (строка)', text: '<span class="trans">Переведённая строка 2</span>' }
    //             ]);
    //         }, 200);
    //     });
    // }
}