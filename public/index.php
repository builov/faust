<?php

use Builov\Faust\Infrastructure\Controller\BuildCacheController;
use Builov\Faust\Infrastructure\Controller\MainPageUpdateApiController;
use Builov\Faust\Infrastructure\Controller\TranslationListApiController;
use Builov\Faust\Infrastructure\DI\ContainerFactory;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;

require_once __DIR__ . '/../vendor/autoload.php';

// Инициализация зависимостей
$DI = ContainerFactory::build();

$isXhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$textId = $_GET['show'] ?? null;
$mode = $_GET['mode'] ?? null;

// Минимальный роутинг
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $lines = $data['lines'] ?? null;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($isXhr) {
//        print_r($data); exit;

        $textIds = $data;

        $controller = $DI[MainPageUpdateApiController::class];
        $response = $controller->handle($lines, $textIds);
    } else {
        $textId = $data['textId'] ?? null;

        if (!$lines || !is_array($lines)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        if (!$textId) {
            $controller = $DI[TranslationListApiController::class];
            $response = $controller->handle($lines);
//    $response = $controller->handle(["3", "4", "5", "6", "7", "8", "9", "10"],'stanza');
        } else {
            $controller = $DI[TranslateLinesApiController::class];
            $response = $controller->handle($lines, $textId);
        }
    }

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
//        'fet',
//        'turgenev'
//        'aksakov'
    ];

    $controller = $DI[MainPageController::class];
    $response = $controller->handle($selected);
}

// Отправляем HTTP заголовки и контент в браузер
$response->send();
