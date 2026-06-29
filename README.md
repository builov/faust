Настройка фронта

# Инициализация проекта (создание package.json)
npm init -y

# Установка Webpack и его CLI как зависимостей разработки
npm install --save-dev webpack webpack-cli

# Для сборки стилей и HTML-страницы потребуются дополнительные плагины и загрузчики. Установите их:
npm install --save-dev html-webpack-plugin css-loader style-loader

# Установка Bootstrap и Popper.js 
npm install bootstrap @popperjs/core

# Установка плагина для сохранения CSS в отдельный файл (вместо style-loader)
npm install --save-dev mini-css-extract-plugin

# Установите Sass и нужные загрузчики:
npm install --save-dev sass sass-loader

# Установите плагин оптимизации CSS:
npm install --save-dev css-minimizer-webpack-plugin

# Установите Babel и его загрузчик:
npm install --save-dev babel-loader @babel/core @babel/preset-env
