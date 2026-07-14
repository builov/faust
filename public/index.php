<?php

use Builov\Faust\Infrastructure\Controller\BuildCacheController;
use Builov\Faust\Infrastructure\Controller\MainPageUpdateApiController;
use Builov\Faust\Infrastructure\Controller\TranslationListApiController;
use Builov\Faust\Infrastructure\DI\ContainerFactory;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;

require_once __DIR__ . '/../vendor/autoload.php';

$DI = ContainerFactory::build();

$isXhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$textId = $_GET['show'] ?? null;
$mode = $_GET['mode'] ?? null; //'reindex' - режим пересборки кеша




if ($isXhr && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $postData = json_decode($json, true);

    /** Асинхронный запрос диапазона строк */
    if (isset($postData['textIds'])) {
        $textIds = $postData['textIds'];

        $range = $_GET['range'] ?? null;

        $controller = $DI[MainPageUpdateApiController::class];
        $response = $controller->handle($range, $textIds);
    }

    /** Асинхронный перевод массива строк (из контекстного меню) */
    elseif (isset($postData['lines'])) {
        $lines = $postData['lines'] ?? null;
        $textId = $postData['textId'] ?? null;

        if (!$lines || !is_array($lines)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        /** запрос списка доступных переводов */
        if (!$textId) {
            $controller = $DI[TranslationListApiController::class];
            $response = $controller->handle($lines);
//    $response = $controller->handle(["3", "4", "5", "6", "7", "8", "9", "10"],'stanza');
        }

        /** запрос строк определенного перевода */
        else {
            $controller = $DI[TranslateLinesApiController::class];
            $response = $controller->handle($lines, $textId);
        }
    }
}
/** Обновление кеша */
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'reindex') {

    $controller = $DI[BuildCacheController::class];
    $response = $controller->handle();

}
/** Загрузка перевода (по нажатию на кнопку) */
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && is_string($textId)) {

    $controller = $DI[SingleTextApiController::class];
    $response = $controller->handle($textId);

}
/** Первичная генерация страницы приложения */
else {
    // параметры для дефолтной страницы
    $selected = [
        'faust',
        'fet',
        'pasternak'
//        'turgenev'
//        'aksakov'
    ];

    $controller = $DI[MainPageController::class];
    $response = $controller->handle($selected);
}

$response->send();
