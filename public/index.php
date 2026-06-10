<?php
use Builov\Faust\Infrastructure\DI\ContainerFactory;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\TranslationApiController;

require_once __DIR__ . '/../vendor/autoload.php';

// Инициализируем зависимости один раз при старте
$DI = ContainerFactory::build();

$textId = $_GET['show'] ?? null;

// Минимальный роутинг
if ($_SERVER['REQUEST_METHOD'] === 'GET' && is_string($textId)) {

    // Передаем управление API-контроллеру
    $controller = $DI[TranslationApiController::class];
    $response = $controller->handle($textId);

} else {
    // параметры для дефолтной страницы
    $selected = [
        'faust',
        'fet',
        'aksakov'
    ];

    // Передаем управление контроллеру главной страницы
    $controller = $DI[MainPageController::class];
    $response = $controller->handle($selected);
}

// Отправляем HTTP заголовки и контент в браузер
$response->send();
