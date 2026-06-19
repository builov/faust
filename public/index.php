<?php

use Builov\Faust\Infrastructure\Controller\BuildCacheController;
use Builov\Faust\Infrastructure\DI\ContainerFactory;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;

require_once __DIR__ . '/../vendor/autoload.php';

// Инициализация зависимостей
$DI = ContainerFactory::build();

$textId = $_GET['show'] ?? null;
$mode = $_GET['mode'] ?? null;

// Минимальный роутинг
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $lines = $data['lines'] ?? null;
    $type = $data['type'] ?? null;

    if (!is_array($lines) || !is_string($type)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid parameters']);
        exit;
    }

    $controller = $DI[TranslateLinesApiController::class];
    $response = $controller->handle($lines, $type);
//    $response = $controller->handle(["3", "4", "5", "6", "7", "8", "9", "10"],'stanza');

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'reindex') {

    $controller = $DI[BuildCacheController::class];
    $response = $controller->handle();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && is_string($textId)) {

    $controller = $DI[SingleTextApiController::class];
    $response = $controller->handle($textId);

} else {
    // параметры для дефолтной страницы
    $selected = [
        'faust',
        'fet',
        'aksakov'
    ];

    $controller = $DI[MainPageController::class];
    $response = $controller->handle($selected);
}

// Отправляем HTTP заголовки и контент в браузер
$response->send();
