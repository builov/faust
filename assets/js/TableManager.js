export class TableManager {
    #table;
    #metaData;
    #onContentUpdated;

    constructor(tableElement, metaData, options) {
        this.#table = tableElement;
        this.#metaData = metaData;
        this.#onContentUpdated = options.onContentUpdated || (() => {});
    }

    get element() {
        return this.#table;
    }

    // Публичные методы для внешнего управления
    addColumn(id, jsonData) {
        this.#disableRender();
        this.#insertDataCells(id, jsonData);
        this.#createHeaderCell(id);
        this.#enableRender();
        this.#setColumnTitle(id);
        this.#onContentUpdated();
    }

    // вариант без удаления таблицы из DOM
    // removeColumn(id) {
    //     const headerCell = this.#table.querySelector(`th[data-text-id="${id}"]`);
    //     if (!headerCell) return;
    //     this.#disableRender();
    //     const colIndex = headerCell.cellIndex;
    //     for (const row of this.#table.rows) {
    //         if (row.cells[colIndex]) row.deleteCell(colIndex);
    //     }
    //     this.#enableRender();
    // }

    // вариант с удалением таблицы из DOM
    removeColumn(id) {
        const headerCell = this.#table.querySelector(`th[data-text-id="${id}"]`);
        if (!headerCell) return;

        const colIndex = headerCell.cellIndex;
        const parent = this.#table.parentNode;

        // 1. Временно убираем таблицу из документа
        const placeholder = document.createComment('table-placeholder');
        parent.replaceChild(placeholder, this.#table);

        // 2. Работаем с уже "оторванной" таблицей — быстро
        for (const row of this.#table.rows) {
            if (row.cells[colIndex]) {
                row.deleteCell(colIndex);
            }
        }

        // 3. Возвращаем таблицу на место — всего одна перерисовка
        parent.replaceChild(this.#table, placeholder);
    }

    hasColumn(id) {
        return !!this.#table.querySelector(`th[data-text-id="${id}"]`);
    }

    // Приватные детали реализации
    #disableRender() {
        this.#table.style.display = 'none';
    }
    #enableRender() {
        this.#table.style.display = '';
    }

    #insertDataCells(id, data) {
        const rows = this.#table.tBodies[0].rows;

        console.log(data);

        Array.from(rows).forEach((row, i) => {
            const cell = row.insertCell(-1);
            const item = data[i];
            if (item) {
                const [text, className] = item;

                if (text.trim().length > 0) {
                    // console.log(text);

                    const div = document.createElement('div');
                    div.className = className;
                    div.innerHTML = text;
                    cell.appendChild(div);

                    cell.closest('tr').classList.add(className);
                }
            }
        });
    }

    #createHeaderCell(id) {
        const theadRow = this.#table.tHead.rows[0];
        const headerCell = document.createElement('th');

        headerCell.dataset.textId = id;
        theadRow.appendChild(headerCell);
    }

    #setColumnTitle(id) {
        const headerCell = this.#table.querySelector(`th[data-text-id="${id}"]`);
        if (!headerCell) return;

        const title = this.#metaData.getTitle(id);
        headerCell.textContent = title;

        const startsFrom = this.#metaData.getStartsFrom(id);
        if (startsFrom.length) {
            this.#addLinksToHeader(headerCell, startsFrom);
        }

        // console.log('#setColumnTitle: ', headerCell);
    }

    #addLinksToHeader(headerCell, startsFrom) {
        // Для одного фрагмента – просто ссылка вокруг заголовка
        if (startsFrom.length === 1) {
            headerCell.innerHTML = `<a href="#${startsFrom[0]}" class="link-secondary">${headerCell.textContent}</a>`;
        }

        // для нескольких фрагментов добавляются ссылки на фрагменты
        else {
            const colIndex = headerCell.cellIndex;

            const listItems = startsFrom.map(path => {
                const row = document.getElementById(path);
                const cellContent = row?.children[colIndex];
                const text = cellContent?.querySelector('.floating-title')?.innerText
                    ?? `«${cellContent?.innerText}»`
                    ?? '';
                return `<li><a href="#${path}" class="link-secondary">${text}</a></li>`;
            }).join('');

            headerCell.insertAdjacentHTML('beforeend', `<ul>${listItems}</ul>`);
        }
    }
}