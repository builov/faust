import { Fetcher } from './Fetcher.js';
import { MetaData } from './MetaData.js';
import { TableManager } from './TableManager.js';
import { ButtonToggleManager } from './ButtonToggleManager.js';
import { InlineCommentController } from './InlineCommentController.js';
import { Loader } from './Loader.js';
import { ScrollToTopButton } from './ScrollToTopButton.js';
import { ContextMenu } from './ContextMenu.js';
import DOMPurify from 'dompurify';

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

        // Переменные для отслеживания движения пальца
        let startX = 0;
        let startY = 0;
        const MOVE_THRESHOLD = 10; // Порог в пикселях, отличающий тап от скролла

        // 1. Фиксируем начальную точку касания
        container.addEventListener('touchstart', (e) => {
            const td = e.target.closest('td');
            if (!td) return;
            const row = td.closest('tr');
            if (!row) return;
            const lineNum = row.dataset.num;
            if (!lineNum) return;

            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        }, { passive: true }); // passive повышает плавность скролла на мобильных

        // 2. Проверяем завершение касания
        container.addEventListener('touchend', (e) => {
            const td = e.target.closest('td');
            if (!td) return;
            const row = td.closest('tr');
            if (!row) return;
            const lineNum = row.dataset.num;
            if (!lineNum) return;

            console.log('touchend ', lineNum);

            const touch = e.changedTouches[0];
            const diffX = Math.abs(touch.clientX - startX);
            const diffY = Math.abs(touch.clientY - startY);

            // Если пользователь сдвинул палец во время касания — это скролл
            if (diffX > MOVE_THRESHOLD || diffY > MOVE_THRESHOLD) {
                return;
            }

            // Если это был чистый короткий тап — обрабатываем действие
            e.preventDefault();
            // Передаём td в первое меню
            this.#showContextMenu(lineNum, touch.clientX, touch.clientY, td);
        });

        // снятие подсветки с диапазона строк
        const events = ['click', 'contextmenu', 'touchstart'];
        events.forEach(eventType => {
            document.addEventListener(eventType, (e) => {
                this.#removeHighlight();
            });
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
        new ContextMenu({ items }).show(x, y);
    }

    /** Определяет массив строк для перевода и вызывает соответствующий метод. */
    #handleTranslation(lineNum, type, x, y, td) {
        // const ids = this.#metaData.getRelatedlineNums(lineNum, type);
        const ids = this.#getRelatedlineNums(lineNum, type);

        // console.log(ids); return;

        this.#highlightRange(ids, td.cellIndex);

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
                action: async () => { // Делаем экшен асинхронным
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

                        this.#removeHighlight();
                        this.initDynamicDialogs();

                    } catch (clickErr) {
                        console.error('Ошибка загрузки текста перевода:', clickErr);
                        alert('Не удалось загрузить текст перевода');
                    } finally {
                        this.#columnLoader.hide();
                    }
                }
            }));

            /* вызов второго контекстного меню */
            new ContextMenu({ items }).show(x, y);
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
                this.#tableManager.addColumn(id, json);
                this.#buttonToggleManager.syncButtonState(btn, id);
            } catch (err) {
                alert(err.message);
            } finally {
                this.#columnLoader.hide();
            }
        });
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
            // Если у элемента нет классов вообще — он является границей диапазона
            if (!line.classList || line.classList.length === 0) {
                return true;
            }

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
                if (type === 'stanza' && current.classList?.contains('stanza_end')) {
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

    #highlightRange(ids, cellIndex) {
        const table = this.#tableManager.element;
        if (!table) return;

        this.#removeHighlight();

        ids.forEach(id => {
            const row = table.querySelector(`tr[data-num="${id}"]`);
            if (row) {
                const cell = row.cells[cellIndex];
                cell.classList.add('highlighted');
            }
        });
    }

    #removeHighlight() {
        const table = this.#tableManager.element;
        if (table) {
            table.querySelectorAll('td.highlighted').forEach(cell => {
                cell.classList.remove('highlighted');
            });
        }
    }
}
