class PoemApp {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        // this.lines = []; //массив строк
        // this.translations = {};
        // this.state = []; //оригинал или перевод
        this.data = null;
        this.activeTranslations = {}; // Храним выбранный ID перевода для каждой строки
    }

    // Асинхронный метод для загрузки данных
    // async loadPoem(url) {
    //     try {
    //         const response = await fetch(url);
    //         const data = await response.json();
    //
    //         // this.lines = data.original;
    //         // this.translations = data.translations;
    //         // this.state = this.lines.map(() => 'original');
    //
    //         this.render();
    //         console.log("Стихотворение загружено успешно");
    //     } catch (error) {
    //         console.error("Ошибка при загрузке:", error);
    //         this.container.innerHTML = "<p>Не удалось загрузить стихотворение.</p>";
    //     }
    // }

    async loadPoem(url) {
        const response = await fetch(url);
        this.data = await response.json();
        this.render();
    }

    // Смена перевода для конкретной строки
    setLineVersion(lineIndex, translationId) {
        this.activeTranslations[lineIndex] = translationId;
        this.render();
    }

    render() {
        if (!this.data) return;
        this.container.innerHTML = '';

        this.data.original.forEach((originalText, i) => {
            const lineWrapper = document.createElement('div');
            lineWrapper.className = 'line-wrapper';

            // 1. Текст строки (оригинал или выбранный перевод)
            const textP = document.createElement('p');
            const selectedId = this.activeTranslations[i];

            // Ищем текст в выбранном переводе
            const translation = this.data.translations.find(t => t.id === selectedId);
            textP.innerText = (translation && translation.lines[i]) ? translation.lines[i] : originalText;

            lineWrapper.appendChild(textP);

            // 2. Выпадающий список для выбора версии (если есть переводы для этой строки)
            const availableTranslations = this.data.translations.filter(t => t.lines[i]);

            if (availableTranslations.length > 0) {
                const select = document.createElement('select');

                // Опция "Оригинал"
                const optOrig = new Option("Оригинал", "");
                select.add(optOrig);

                // Опции переводов
                availableTranslations.forEach(t => {
                    const opt = new Option(t.author, t.id);
                    opt.selected = selectedId === t.id;
                    select.add(opt);
                });

                select.onchange = (e) => this.setLineVersion(i, e.target.value);
                lineWrapper.appendChild(select);
            }

            this.container.appendChild(lineWrapper);
        });
    }

    // Метод для перевода всех строк сразу
    //todo добавить выбор перевода
    translateAll() {
        this.state = this.state.map(() => 'translated');
        this.render();
    }

    // Метод для переключения одной строки (туда-обратно)
    //todo сделать контекст. меню со списком переводов
    toggleLine(index) {
        if (this.translations[index]) {
            this.state[index] = this.state[index] === 'original' ? 'translated' : 'original';
            this.render();
        }
    }

    // Метод для замены группы строк (принимает массив индексов)
    replaceGroup(indices) {
        indices.forEach(index => {
            if (this.translations[index]) this.state[index] = 'translated';
        });
        this.render();
    }

    // Метод для сброса всего стихотворения
    reset() {
        this.state = this.lines.map(() => 'original');
        this.render();
    }
}

const app = new PoemApp('poem-container');

app.loadPoem('data/poem2.json');
