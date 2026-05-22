// Внедряем глобальный crypto для старых версий Node.js
if (!globalThis.crypto) {
    globalThis.crypto = require('crypto').webcrypto || require('crypto');
}

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, argv) => {
    // Проверяем, запущен ли режим production
    const isProduction = argv.mode === 'production';

    return {
        // Режим сборки: 'development' (для разработки) или 'production' (минификация кода)
        // mode: 'development',
        // Режим выставляется автоматически из команды запуска
        mode: isProduction ? 'production' : 'development',

        // Включаем карты кода (Source Maps) только для разработки, чтобы легко искать ошибки
        devtool: isProduction ? false : 'source-map',

        // Точка входа: файл, с которого Webpack начинает сборку проекта
        entry: './assets/js/index.js',

        // Точка выхода: куда складывать готовый бандл
        output: {
            filename: 'js/bundle.js', // Скрипт ляжет в dist/js/bundle.js
            path: path.resolve(__dirname, 'public/dist'),
            clean: true, // Очищает папку dist перед новой сборкой
        },

        // Правила обработки файлов (загрузчики/loaders)
        module: {
            rules: [
                {
                    // ПРАВИЛО ДЛЯ BABEL
                    test: /\.m?js$/,
                    exclude: /node_modules/, // Не трогаем код в папке node_modules
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env'] // Умный пресет, который знает, какие фичи транспилировать
                        }
                    }
                },
                {
                    // Настраиваем проверку для .scss или .sass файлов
                    test: /\.s[ac]ss$/i,
                    use: [
                        MiniCssExtractPlugin.loader, // 3. Извлекает CSS в отдельный файл
                        'css-loader',                // 2. Переводит CSS в CommonJS модули
                        'sass-loader'                // 1. Компилирует Sass/SCSS в обычный CSS
                    ],
                },
                {
                    // === 3. НОВОЕ ПРАВИЛО ДЛЯ КАРТИНОК ===
                    test: /\.(png|s?gz|jpg|jpeg|gif|webp)$/i,
                    type: 'asset', // Автоматически выберет: встроенная строка или отдельный файл
                    parser: {
                        dataUrlCondition: {
                            maxSize: 8 * 1024 // Картинки меньше 8 Кб встроятся прямо в CSS как Base64
                        }
                    },
                    generator: {
                        // Куда копировать крупные картинки (с добавлением хэша в имя для кэша)
                        filename: 'images/[name].[hash:6][ext]'
                    }
                },
                {
                    // === 4. НОВОЕ ПРАВИЛО ДЛЯ ШРИФТОВ ===
                    test: /\.(woff|woff2|eot|ttf|otf)$/i,
                    type: 'asset/resource', // Шрифты всегда копируются как отдельные файлы
                    generator: {
                        filename: 'fonts/[name].[hash:6][ext]'
                    }
                }
            ],
        },

        // Плагины для расширения возможностей Webpack
        plugins: [
            // new HtmlWebpackPlugin({
            //     title: 'Мой Webpack Проект', // Автоматически создаст index.html с подключенным bundle.js
            // }),
            // Настраиваем, куда складывать собранный CSS файл
            new MiniCssExtractPlugin({
                filename: 'css/main.css', // Файл ляжет в public/dist/css/main.css
            }),
        ],

        // Настройки оптимизации и сжатия
        optimization: {
            minimize: isProduction, // Включаем минификацию только на продакшене
            minimizer: [
                `...`, // Три точки сохраняют стандартный плагин сжатия JS (Terser)
                new CssMinimizerPlugin(), // Добавляем сжатие CSS
            ],
        },
    };
};
