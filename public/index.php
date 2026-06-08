<?php
use Builov\Faust\Infrastructure\DI\ContainerFactory;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\TranslationApiController;

require_once __DIR__ . '/../vendor/autoload.php';

// Инициализируем зависимости один раз при старте
$container = ContainerFactory::build();

$showParam = $_GET['show'] ?? null;

// Минимальный роутинг
if ($_SERVER['REQUEST_METHOD'] === 'GET' && is_string($showParam)) {

    // Передаем управление API-контроллеру
    $controller = $container[TranslationApiController::class];
    $response = $controller->handle($showParam);

} else {

    // Формируем параметры для дефолтной страницы
    $selected = is_array($showParam) ? $showParam : ['faust', 'fet', 'guber'];

    // Передаем управление контроллеру главной страницы
    $controller = $container[MainPageController::class];
    $response = $controller->handle($selected);
}

// Отправляем HTTP заголовки и контент в браузер
$response->send();
