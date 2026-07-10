import { Fetcher } from './Fetcher.js';
import { MetaData } from './MetaData.js';
import { TableManager } from './TableManager.js';
import { ButtonToggleManager } from './ButtonToggleManager.js';
import { InlineCommentController } from './InlineCommentController.js';
import { Loader } from './Loader.js';
import { ScrollToTopButton } from './ScrollToTopButton.js';
import { ContextMenu } from './ContextMenu.js';
import DOMPurify from 'dompurify';
// import { NotificationModal } from './NotificationModal.js';
import { ToastNotification } from './ToastNotification.js';

export class App {
    #metaData;
    #fetcher;
    #tableManager;
    #buttonToggleManager;
    #dialogController;
    #columnLoader;

    constructor() {
        // Блокируем интерфейс до завершения init
        // document.body.classList.add('app-loading'); перенесено в html-код

        this.#columnLoader = new Loader();
        // Если в HTML есть готовый контейнер, можно передать:
        // this.#loader = new Loader(document.getElementById('loading-indicator'));

        const rawMeta = JSON.parse(document.getElementById('meta-data').textContent);
        this.#metaData = new MetaData(rawMeta);

        this.#fetcher = new Fetcher();

        const mainContainer = document.querySelector('.main-container');

        // Создаём менеджеры, зависящие от метаданных и DOM
        this.#tableManager = new TableManager(
            mainContainer,
            this.#metaData,
            {
                onContentUpdated: () => this.initDynamicDialogs()
            }
        );

        this.#buttonToggleManager = new ButtonToggleManager(this.#tableManager);

        this.#dialogController = new InlineCommentController(mainContainer);

        this.#initContextMenu();

        // Инициализация кнопки «наверх»
        new ScrollToTopButton();
    }

    init() {
        console.log('app init start');

        this.#initFilterButtons();
        this.#initNavLinks();
        this.initDynamicDialogs();

        // Снятие блокировки кнопок
        document.body.classList.remove('app-loading');
        console.log('app init end');
    }

    // ---------- КОНТЕКСТНОЕ МЕНЮ ПЕРЕВОДОВ ----------
    #initContextMenu() {
        const container = document.querySelector('.main-container');
        if (!container) return;

        /* добавление события на вызов контекстного меню */
        container.addEventListener('contextmenu', (event) => {
            const td = event.target.closest('td');
            if (!td) return;
            const row = td.closest('tr');
            if (!row) return;
            const lineNum = row.dataset.num;
            if (!lineNum) return;

            event.preventDefault();
            // Передаём td в первое меню
            this.#showContextMenu(lineNum, event.clientX, event.clientY, td);
        });
    }

    /* вызов первого контекстного меню */
    #showContextMenu(lineNum, x, y, td) {
        const items = [
            { label: 'Перевести строку', action: () => this.#handleTranslation(lineNum, 'line', x, y, td) },
            { label: 'Перевести строфу', action: () => this.#handleTranslation(lineNum, 'stanza', x, y, td) },
            { label: 'Перевести реплику', action: () => this.#handleTranslation(lineNum, 'speech', x, y, td) },
            // { label: 'Перевести всё', action: () => this.#handleTranslation(lineNum, 'all', x, y, td) },
        ];

        /* вызов первого контекстного меню */
        new ContextMenu({ items, x, y }).show();
    }

    /** Определяет массив строк для перевода и вызывает соответствующий метод. */
    #handleTranslation(lineNum, type, x, y, td) {
        // const ids = this.#metaData.getRelatedlineNums(lineNum, type);
        const ids = this.#getRelatedlineNums(lineNum, type);

        // console.log(ids); return;

        this.#showTranslationsMenu(ids, type, x, y, td).then(r => {
            console.log('переведены строки: ', ids);
        });

    }

    /* вызов второго контекстного меню */
    async #showTranslationsMenu(lineNums, type, x, y, td) {
        /** @type {import('dompurify').Config} */
        const purifyConfig = {
            ALLOWED_TAGS: ['b', 'i', 'strong', 'em', 'a', 'br', 'span', 'p', 'dialog'],
            ALLOWED_ATTR: ['href', 'target', 'title', 'class'], // Разрешаются ссылки и оформление, но блокируется onclick/onerror
            RETURN_TRUSTED_TYPE: false // false для совместимости с innerHTML
        };

        // console.log('td: ', td);
        try {
            this.#columnLoader.show();
            const translationList = await this.#fetcher.fetchTranslationList(lineNums, type);

            if (!translationList || translationList.length === 0) {
                alert('Нет доступных переводов');
                return;
            }

            const colIndex = td.cellIndex;

            // Формирование пунктов меню
            const items = translationList.map(item => ({
                label: item.title, // Отображение title в контекстном меню
                action: async () => {
                    try {
                        this.#columnLoader.show(); // лоадер на время дозагрузки текста

                        // ШАГ 2: Запрашиваем текст конкретного перевода по его label
                        // Ожидается ответ вида: { "5616": "...", "5617": "..." } или объект с полем texts
                        const data = await this.#fetcher.fetchTranslationText(lineNums, item.label);

                        // Защита на случай, если сервер вернет объект с текстами внутри поля texts или напрямую
                        const texts = data.texts || data;

                        // Вставляем полученный текст в DOM
                        Object.entries(texts).forEach(([lineNum, text]) => {
                            const row = document.querySelector(`tr[data-num="${lineNum}"]`);

                            if (row && row.cells[colIndex]) {
                                const cell = row.cells[colIndex];
                                const child = cell.firstElementChild;

                                const safeHtml = DOMPurify.sanitize(text, purifyConfig);

                                if (child) {
                                    child.innerHTML = safeHtml;
                                } else {
                                    cell.innerHTML = safeHtml;
                                }
                            }
                        });

                        this.initDynamicDialogs();

                    } catch (clickErr) {
                        console.error('Ошибка загрузки текста перевода:', clickErr);
                        alert('Не удалось загрузить текст перевода');
                    } finally {
                        this.#columnLoader.hide();
                    }
                }
            }));

            /* непосредственно вызов второго контекстного меню */
            new ContextMenu({ items, x: x, y }).show();
        } catch (err) {
            console.error('Ошибка загрузки переводов:', err);
            alert('Не удалось загрузить переводы');
        } finally {
            this.#columnLoader.hide();
        }
    }

    initDynamicDialogs() {
        // Находим комментарии и передаем их в контроллер, чтобы он сгенерировал для них кнопки "*"
        this.#dialogController.init(document.querySelectorAll('.main-container dialog'));
    }

    #initFilterButtons() {
        const buttonsContainer = document.querySelector('#buttons');

        buttonsContainer.addEventListener('click', async (event) => {
            event.preventDefault();

            const btn = event.target.closest('.btn');

            if (!btn || !buttonsContainer.contains(btn)) {
                return;
            }

            const id = btn.dataset.id;

            // Если колонка уже есть — удаляем
            if (this.#tableManager.hasColumn(id)) {
                this.#columnLoader.show();

                await new Promise(resolve => setTimeout(resolve, 0));

                this.#tableManager.removeColumn(id);
                this.#buttonToggleManager.syncButtonState(btn, id);

                this.#columnLoader.hide();
                return;
            }

            try {
                this.#columnLoader.show();

                const json = await this.#fetcher.getJson(btn.href);

                if (json && json.length > 0) {
                    this.#tableManager.addColumn(id, json);
                    this.#buttonToggleManager.syncButtonState(btn, id);
                } else {
                    const urlParams = new URLSearchParams(window.location.search);
                    const range = urlParams.get('range'); // Вернет "alex"

                    let message = '';

                    if (range === null) {
                        message = 'В этом переводе нет соответствующих строк.';
                    } else {
                        message = `В этом переводе нет строк ${range}.`;
                    }

                    ToastNotification.show(message);
                }
            } catch (err) {
                alert(err.message);
            } finally {
                this.#columnLoader.hide();
            }
        });
    }

    #initNavLinks() {
        const nav = document.querySelector('nav');
        if (!nav) return;

        nav.addEventListener('click', async (event) => {
            const link = event.target.closest('a');
            if (!link) return;

            event.preventDefault();

            const href = link.getAttribute('href');

            const elements = document.querySelectorAll('[data-text-id]');
            const textIds = Array.from(elements).map(el => el.dataset.textId);

            const json = await this.#fetcher.postJson(href, { textIds });

            // console.log(json);

            this.#updateTable(json);

            // прокрутка к выбранному в оглавлении пункту
            // const match = href.match(/[?&]lines=(\d+)/);
            // if (match) {
            //     const firstLine = match[1];
            //     const target = document.getElementById(firstLine);
            //     if (target) {
            //         target.scrollIntoView({behavior: 'smooth', block: 'start'});
            //     }
            // }

            history.pushState(null, '', href);
        });
    }

    #updateTable(inputData) {
        // 1. список всех текстов
        const keys = Object.keys(inputData);

        if (keys.length === 0) {
            return '';
        }

        // 2. Берем первый текст за эталон для прохода по строкам
        const baseSource = inputData[keys[0]];

        // 3. Собираем HTML-строки
        const tableRowsHtml = baseSource.map((_, index) => {
            // Получаем ID и класс из текущей строки первого текста
            const [,, id] = baseSource[index];
            const [, className] = baseSource[index];

            // Генерируем ячейки <td> для каждого текста по текущему индексу строки
            const cellsHtml = keys.map(key => {
                if (inputData && inputData[key] && inputData[key][index]) {
                    const text = inputData[key][index][0]; // Берем текст [0] из соответствующего массива
                    return `<td><div class="${className}">${text}</div></td>`;
                } else {
                    return `<td><div></div></td>`;
                }
            }).join('');

            // Собираем итоговую строку <tr>
            return `
            <tr data-num="${id}" class="${className}" id="${id}">
                <td>${id}</td>
                ${cellsHtml}
            </tr>
            `.trim();

        }).join('\n');

        // Обновляем тело таблицы
        const tbody = document.querySelector('.main-container tbody');
        if (tbody) {
            tbody.innerHTML = tableRowsHtml;
        } else {
            console.error('Элемент tbody внутри .main-container не найден');
        }

        // 3. СИНХРОНИЗАЦИЯ ШАПКИ ТАБЛИЦЫ (<thead>)
        const theadRow = document.querySelector('.main-container thead tr');

        if (theadRow) {
            // Находим самую первую ячейку (номер строки), которую нельзя удалять
            const firstTh = theadRow.querySelector('th:not([data-text-id])') || document.createElement('th');

            // Очищаем строку заголовка
            theadRow.innerHTML = '';

            // Возвращаем на место первую колонку номеров
            theadRow.appendChild(firstTh);

            // Проходим по ключам из JSON и добавляем их в шапку в правильном порядке
            keys.forEach(textId => {
                // обновление заголовка
                const newTh = document.createElement('th');
                newTh.setAttribute('data-text-id', textId);
                newTh.textContent = this.#metaData.getTitle(textId);
                theadRow.appendChild(newTh);

                const startsFrom = this.#metaData.getStartsFrom(textId);

                if (startsFrom.length) {
                    this.#tableManager.addLinksToHeader(newTh, startsFrom);
                }
            });
        }

        // 4. Обновляем CSS-классы кнопок фильтров/переводов
        const buttons = document.querySelectorAll('#buttons a[data-id]');
        buttons.forEach(btn => {
            const btnId = btn.getAttribute('data-id');

            if (keys.includes(btnId)) {
                btn.classList.add('btn-secondary');
                btn.classList.remove('btn-outline-secondary');
            } else {
                btn.classList.add('btn-outline-secondary');
                btn.classList.remove('btn-secondary');
            }
        });

        // 5. ЗАКРЫВАЕМ ОГЛАВЛЕНИЕ
        const details = document.getElementById('table-of-contents').firstElementChild;
        if (details) {
            details.removeAttribute('open');
        }
    }

    /**
     * Возвращает массив data-num строк для заданного типа контекста.
     * Использует CSS-классы строк таблицы для поиска границ.
     * @param {string} lineNum - data-num строки, с которой начали
     * @param {'line'|'stanza'|'speech'} type
     * @returns {string[]}
     */
    #getRelatedlineNums(lineNum, type) {
        if (type === 'line') {
            return [lineNum];
        }

        const table = this.#tableManager.element;
        if (!table || (type !== 'stanza' && type !== 'speech')) {
            return [lineNum];
        }

        const startRow = table.querySelector(`tr[data-num="${lineNum}"]`);
        if (!startRow) {
            return [lineNum];
        }

        const isSpeech = type === 'speech';

        // Функция проверки: является ли строка границей контекста
        const isBoundary = (line) => {
            for (const cls of line.classList) {
                if (cls === 'default') {
                    continue;
                }
                if (isSpeech && (cls === 'stanza_end' || cls === 'stage_direction')) {
                    continue;
                }

                return true;
            }

            return false;
        };

        // Поиск начала диапазона: первая строка после ближайшей границы сверху
        let beginRow = table.rows[0];
        let current = startRow.previousElementSibling;

        while (current) {
            if (isBoundary(current)) {
                beginRow = current.nextElementSibling;
                break;
            }

            current = current.previousElementSibling;
        }

        // Поиск конца диапазона: последняя строка перед ближайшей границей снизу
        let endRow = table.rows[table.rows.length - 1];
        current = startRow.nextElementSibling;

        while (current) {
            if (isBoundary(current)) {
                if (type === 'stanza' && current.classList.contains('stanza_end')) {
                    endRow = current; // включить строку с классом stanza_end
                } else {
                    endRow = current.previousElementSibling; // закончить перед границей
                }
                break;
            }

            current = current.nextElementSibling;
        }

        // Сбор data-num от beginRow до endRow включительно
        const ids = [];
        let row = beginRow;

        while (row) {
            const num = row.dataset.num;

            if (num) {
                ids.push(num);
            }
            if (row === endRow) {
                break;
            }

            row = row.nextElementSibling;
        }

        return ids;
    }
}
