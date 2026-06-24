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
     * Получить список переводов для группы строк (или одной строки)
     * @param {string[]} lines - массив идентификаторов строк (data-num)
     * @param {string} type - 'line' | 'strophe' | 'replica'
     * @returns {Promise<Array<{label: string, title: string}>>}
     */
    async fetchTranslationList(lines, type) {
        return this.postJson('/', { lines });
    }

    /**
     * Получить список переводов для группы строк (или одной строки)
     * @param {string[]} lines - массив идентификаторов строк (data-num)
     * @param {string} textId
     * @returns {Promise<Array<{rowId: string, text: string}>>}
     */
    async fetchTranslationText(lines, textId) {
        return this.postJson('/', { lines, textId });
    }
}