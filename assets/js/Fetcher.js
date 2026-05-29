export class Fetcher {
    async getJson(url) {
        return this.#request(url);
    }

    async postJson(url, body) {
        return this.#request(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
    }

    async #request(url, options = {}) {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }
        return response.json();
    }
}