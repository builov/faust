const buttons = document.querySelector('#buttons');

buttons.addEventListener('click', async (event) => {
    event.preventDefault();

    const btn = event.target.closest('.btn');

    if (btn && buttons.contains(btn)) {
        const id = btn.dataset.id;
        console.log("Действие для ID:", id);

        if (document.querySelector(`[data-text-id="${id}"]`)) {
            console.log('Элемент найден.');
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

// Формируем строку HTML из массива
//         const html = json.map(([text, className]) => {
//             return `<div class="${className}">${text}</div>`;
//         }).join('');

// Добавляем в конец контейнера (перед закрывающим тегом)
//         container.insertAdjacentHTML('beforeend', html);




        updateTable(json, id);


    }
});

const updateTable = (data, id) => {
    const table = document.querySelector('.main-container');
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
            div.textContent = text;
            cell.appendChild(div);
        }
    });


    // добавление ячейки с id текста в thead
    const theadRow = table.tHead.rows[0];
    const headerCell = document.createElement('th');
    headerCell.dataset.textId = id;
    theadRow.appendChild(headerCell);

    // 4. Включаем отрисовку обратно
    table.style.display = '';
};

