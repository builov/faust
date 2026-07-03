export class Fetcher {
    // Приватный метод для автоматического добавления параметра lines к любому URL
    // #prepareUrl(url) {
    //     const urlObj = new URL(url, window.location.origin);
    //     const currentParams = new URLSearchParams(window.location.search);
    //
    //     if (currentParams.has('lines')) {
    //         urlObj.searchParams.set('lines', currentParams.get('lines'));
    //     }
    //
    //     return urlObj.toString();
    // }

    // более универсальный метод
    #prepareUrl(url) {
        const urlObj = new URL(url, window.location.origin);
        const currentParams = new URLSearchParams(window.location.search);

        // Проходим по всем параметрам из адресной строки браузера
        for (const [key, value] of currentParams.entries()) {
            // Если параметра нет в целевом URL — копируем его из браузера
            if (!urlObj.searchParams.has(key)) {
                urlObj.searchParams.set(key, value);
            }
        }

        return urlObj.toString();
    }


    async getJson(url) {

        // console.log('url: ', url);

        const finalUrl = this.#prepareUrl(url);

        // console.log('finalUrl: ', finalUrl);

        const response = await fetch(finalUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }
        return response.json();
    }

    async postJson(url, body) {
        const finalUrl = this.#prepareUrl(url);

        const response = await fetch(finalUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)  //body: JSON.stringify({ some: 'data' })
        });

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }
        return response.json();
    }

    // async getJson(url) {
    //     const response = await fetch(url);
    //     if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
    //     return response.json();
    // }


    // async postJson(url, body) {
    //     const response = await fetch(url, {
    //         method: 'POST',
    //         headers: { 'Content-Type': 'application/json' },
    //         body: JSON.stringify(body)
    //     });
    //     if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
    //     return response.json();
    // }

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