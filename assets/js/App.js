import { Fetcher } from './Fetcher.js';
import { MetaData } from './MetaData.js';
import { TableManager } from './TableManager.js';
import { ButtonToggleManager } from './ButtonToggleManager.js';
import { DialogController } from './DialogController.js';
import { Loader } from './Loader.js';
import { ScrollToTopButton } from './ScrollToTopButton.js';

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

        // Создаём менеджеры, зависящие от метаданных и DOM
        this.#tableManager = new TableManager(
            document.querySelector('.main-container'),
            this.#metaData
        );

        this.#buttonToggleManager = new ButtonToggleManager(this.#tableManager);

        this.#dialogController = new DialogController();

        // Инициализация кнопки «наверх»
        new ScrollToTopButton();

        // console.log('app constructor start');
        //
        // this.#fetcher = new Fetcher();
        //
        // // Блокируем интерфейс до завершения init
        // document.body.classList.add('app-loading');
        //
        // this.#columnLoader = new Loader();
        // // Если в HTML есть готовый контейнер, можно передать:
        // // this.#loader = new Loader(document.getElementById('loading-indicator'));
        //
        // console.log('app constructor end');
    }

    init() {
        console.log('app init start');

        this.#initFilterButtons();
        this.#dialogController.init(document.querySelectorAll('dialog'));

        // try {
        //     // 1. Загружаем метаданные (POST-запрос на нужный эндпоинт)
        //     // const rawMeta = await this.#fetcher.postJson('/meta.php', { receive: 'config' });
        //     const rawMeta = JSON.parse(document.getElementById('meta-data').textContent);
        //     this.#metaData = new MetaData(rawMeta);
        //
        //     // 2. Создаём менеджеры, зависящие от метаданных и DOM
        //     this.#tableManager = new TableManager(
        //         document.querySelector('.main-container'),
        //         this.#metaData
        //     );
        //     this.#buttonToggleManager = new ButtonToggleManager(this.#tableManager);
        //     this.#dialogController = new DialogController();
        //
        //     // 3. Запускаем UI-логику
        //     this.#initFilterButtons();
        //     this.#dialogController.init(document.querySelectorAll('dialog'));
        // } catch (error) {
        //     console.error('Не удалось инициализировать приложение:', error);
        // } finally {
        //     document.body.classList.remove('app-loading');
        // }

        // Снятие блокировки кнопок
        document.body.classList.remove('app-loading');
        console.log('app init end');
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
}
