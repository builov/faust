export class MetaData {
    #data;

    constructor(data) {
        this.#data = data;

        console.log(data);
    }

    getTitle(textId) {
        return this.#data[textId]?.title ?? '';
    }

    getStartsFrom(textId) {
        return this.#data[textId]?.starts_from ?? [];
    }

    /**
     * Возвращает массив идентификаторов строк (data-num),
     * входящих в тот же контекст, что и переданный lineNum.
     * @param {string} lineNum – data-num исходной строки
     * @param {'line'|'strophe'|'replica'} type
     * @returns {string[]}
     */
    getRelatedlineNums(lineNum, type) {
        // Для одной строки возвращаем массив с ней самой
        if (type === 'line') {
            return [
                lineNum,
                // String(parseInt(lineNum) + 1),
                // String(parseInt(lineNum) + 2)
            ];
        }

        if (type === 'stanza' || type === 'speech') {

        }

        // Здесь должна быть реальная логика на основе структуры данных.
        // Пример: данные хранятся в this.#data.rows[lineNum] с полями strophe / replica,
        // содержащими массив идентификаторов строк.
        // const rowInfo = this.#data.rows?.[lineNum];
        //
        // if (rowInfo) {
        //     if (type === 'stanza' && Array.isArray(rowInfo.stanza)) {
        //         return rowInfo.strophe;
        //     }
        //     if (type === 'speech' && Array.isArray(rowInfo.speech)) {
        //         return rowInfo.replica;
        //     }
        // }

        // Заглушка, если данных нет: возвращаем только саму строку
        return [lineNum];
    }
}