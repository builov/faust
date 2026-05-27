import 'bootstrap';
import '../css/./style.scss';

// import logoImg from '../images/logo.png'; //импорт картинок для динамической вставки
// const img = document.createElement('img');
// img.src = logoImg; // Ссылка на скопированный файл в public/dist/images/
// document.body.appendChild(img);

const textMetaData = JSON.parse(document.getElementById('meta-data').textContent);
// console.log(textMetaData);

const buttons = document.querySelector('#buttons');
const table = document.querySelector('.main-container');

buttons.addEventListener('click', async (event) => {
    event.preventDefault();

    const btn = event.target.closest('.btn');

    if (btn && buttons.contains(btn)) {
        const id = btn.dataset.id;
        console.log("Действие для ID:", id);

        if (document.querySelector(`[data-text-id="${id}"]`)) {
            console.log('Элемент найден.');

            deleteColumnById(id);

            setButtonState(btn, id);

            return;
        }

        const url = btn.href;

        let response = await fetch(url);
        let json = '';

        if (response.ok) {
            json = await response.json();
        } else {
            alert("Ошибка HTTP: " + response.status);
        }

        // console.log(json[0]);

        // const container = document.querySelector('.main-container');
        //
        // console.log(container);

        updateTable(json, id);

        setButtonState(btn, id);
    }
});

const updateTable = (data, id) => {
    if (!table) return;

    // console.log(data);

    // 1. Создаем карту (индекс) строк один раз. Это уравнивает скорость data-атрибутов со скоростью ID
    const rowMap = new Map();
    for (let i = 0; i < table.rows.length; i++) {
        const row = table.rows[i];
        const num = row.dataset.num; // Берет значение из data-num
        if (num) rowMap.set(num, row);
    }

    // 2. Отключаем отрисовку таблицы (критически важно для скорости)
    table.style.display = 'none';

    // 3. Быстро разносим данные. Проходим по всем строкам таблицы
    Array.from(table.tBodies[0].rows).forEach((row, i) => {
        const cell = row.insertCell(-1);

        // Ищем данные в массиве по индексу
        const item = data[i];

        if (item) {
            const [text, className] = item;
            const div = document.createElement('div');
            div.className = className;
            // div.textContent = text;
            div.innerHTML = text;
            cell.appendChild(div);
        }
    });


    // добавление ячейки с id текста в thead
    const theadRow = table.tHead.rows[0];
    const headerCell = document.createElement('th');
    headerCell.dataset.textId = id;
    theadRow.appendChild(headerCell);

    setColumnTitle(id);

    // 4. Включаем отрисовку обратно
    table.style.display = '';
};

const deleteColumnById = (textId) => {
    // 1. Ищем заголовок именно с этим ID
    const headerCell = table.querySelector(`th[data-text-id="${textId}"]`);

    if (!headerCell) return;

    // 2. Получаем актуальный индекс колонки
    const colIndex = headerCell.cellIndex;

    // 3. Отключаем отрисовку для скорости
    table.style.display = 'none';

    // 4. Удаляем ячейки во всех секциях (thead и tbody)
    const rows = table.rows;
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].cells[colIndex]) {
            rows[i].deleteCell(colIndex);
        }
    }

    table.style.display = '';
};

const setColumnTitle = (textId) => {
    const headerCell = table.querySelector(`th[data-text-id="${textId}"]`);

    if (!headerCell) return;

    headerCell.textContent = textMetaData[textId].title;

    if (textMetaData[textId]?.starts_from) {
        let startsFrom = textMetaData[textId].starts_from;

        if (startsFrom.length > 1) { //если больше одного фрагмента

            //получение индекса ячейки в ряду
            let index = 0;
            let element = headerCell;
            while ((element = element.previousElementSibling)) {
                index++;
            }

            const listItems = startsFrom.map((path) => {
                const row = document.getElementById(path);
                // return `<li><a href="#${path}" class="link-secondary">${row.children[index].querySelector('.floating-title').innerText}</a></li>`;
                // return `<li><a href="#${path}" class="link-secondary">
                //         ${row.children[index]?.querySelector('.floating-title')?.innerText ?? `«${row.children[index]?.innerText}»` ?? ''}
                //         </a></li>`;
                return `<li><a href="#${path}" class="link-secondary">
                    ${row.children[index]?.querySelector('.floating-title')?.innerText ?? `«${row.children[index]?.innerText}»` ?? ''}
                    </a></li>`;

            }).join('');

            const links = `<ul>${listItems}</ul>`;

            headerCell.insertAdjacentHTML('beforeend', links);
        }

        else { //если фрагмент один
            headerCell.innerHTML = `<a href="#${startsFrom[0]}" class="link-secondary">${headerCell.textContent}</a>`;
        }
    }
}

const setButtonState = (button, textId) => {
    const headerCell = table.querySelector(`th[data-text-id="${textId}"]`);

    if (headerCell) {
        button.classList.replace('btn-outline-secondary', 'btn-secondary');
    } else {
        button.classList.replace('btn-secondary', 'btn-outline-secondary');
    }
}






/* Комментарий
// 1. Находим все элементы-обёртки
const wrappers = document.querySelectorAll('.tooltip-wrapper');

wrappers.forEach(wrapper => {
    const btn = wrapper.querySelector('.comment-trigger');
    const tooltip = wrapper.querySelector('.tooltip-text');
    const closeBtn = wrapper.querySelector('.tooltip-close');

    // Клик по звёздочке
    btn.addEventListener('click', (event) => {
        event.stopPropagation();

        // Сначала закрываем все остальные открытые подсказки
        document.querySelectorAll('.tooltip-text.is-active').forEach(openTooltip => {
            if (openTooltip !== tooltip) {
                openTooltip.classList.remove('is-active');
            }
        });

        tooltip.classList.toggle('is-active');
    });

    // Клик по крестику закрывает подсказку
    closeBtn.addEventListener('click', (event) => {
        event.stopPropagation(); // Стопаем всплытие, чтобы не срабатывали другие клики
        tooltip.classList.remove('is-active');
    });

    // Защита: клик внутри самой подсказки не должен её закрывать
    tooltip.addEventListener('click', (event) => {
        event.stopPropagation();
    });
});

// Клик в любом другом месте экрана закрывает все открытые подсказки
document.addEventListener('click', () => {
    document.querySelectorAll('.tooltip-text.is-active').forEach(tooltip => {
        tooltip.classList.remove('is-active');
    });
});
*/



const dialogs = document.querySelectorAll('dialog');

dialogs.forEach(dialog => {
    // 1. Создаем и вставляем звёздочку перед тегом <dialog>
    const trigger = document.createElement('button');
    trigger.className = 'comment-trigger';
    trigger.textContent = '*';
    dialog.before(trigger);

    // Функция для удаления крестика при закрытии
    const removeCloseButton = () => {
        const existingCloseBtn = dialog.querySelector('.dialog-close');
        if (existingCloseBtn) existingCloseBtn.remove();
    };

    // 2. Логика клика по звёздочке
    trigger.addEventListener('click', (event) => {
        event.stopPropagation();

        // Закрываем другие открытые диалоги на странице
        document.querySelectorAll('dialog[open]').forEach(openDialog => {
            if (openDialog !== dialog) {
                openDialog.close();
            }
        });

        if (!dialog.open) {
            // ДИНАМИЧЕСКОЕ СОЗДАНИЕ КНОПКИ ЗАКРЫТИЯ ПРИ ВСПЛЫТИИ:
            const closeBtn = document.createElement('button');
            closeBtn.className = 'dialog-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Закрыть');

            // Вставляем крестик в самое начало контента внутри dialog
            dialog.prepend(closeBtn);

            // Вешаем событие закрытия на созданный крестик
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dialog.close();
            });

            // Открываем окно
            dialog.show();

            // УМНАЯ АВТОПРОКРУТКА:
            // Используем setTimeout(..., 50), чтобы диалог сначала отобразился в DOM,
            // иначе браузер не сможет правильно рассчитать его размеры и положение.
            setTimeout(() => {
                dialog.scrollIntoView({
                    behavior: 'smooth', // Плавная анимация скролла
                    block: 'nearest',   // Прокрутит ровно настолько, чтобы элемент полностью влез в экран
                    inline: 'nearest'   // Защита от горизонтального выезда на мобильных
                });
            }, 50);
        } else {
            dialog.close();
        }
    });

    // Нативный метод тега <dialog> генерирует событие 'close' при закрытии окна
    dialog.addEventListener('close', removeCloseButton);

    // Клик внутри самого диалога не закрывает его
    dialog.addEventListener('click', (event) => {
        event.stopPropagation();
    });
});

// Клик в любом месте экрана закрывает все открытые диалоги
document.addEventListener('click', () => {
    document.querySelectorAll('dialog[open]').forEach(dialog => {
        dialog.close();
    });
});


