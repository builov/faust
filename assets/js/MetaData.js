export class MetaData {
    #data;

    constructor(data) {
        this.#data = data;
    }

    getTitle(textId) {
        return this.#data[textId]?.title ?? '';
    }

    getStartsFrom(textId) {
        return this.#data[textId]?.starts_from ?? [];
    }
}