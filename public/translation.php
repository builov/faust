<?php
use Builov\Faust\LinesRepository;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

//if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lines']) && is_array($_POST['lines'])) {
//    $line = (int) $_POST['lines'];
//    if (!$line) {
//        return;
//    }

//    $lines = filter_input(INPUT_POST, 'lines', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
//    $lines = array_filter($lines);

$rawInput = file_get_contents('php://input');
$postData = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) { // Проверяем, что JSON корректен и содержит нужные ключи
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$lines = $postData['lines'] ?? [];
$type  = $postData['type']  ?? '';

if (!is_array($lines) || !is_string($type)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$repo = new LinesRepository();

header('Content-Type: application/json; charset=utf-8');

//if (count($lines) === 1) {
////    echo json_encode($repo::getTranslationsForLine($lines[0]), JSON_UNESCAPED_UNICODE);
//    echo json_encode($repo::getTranslationsForLines([$lines[0]]), JSON_UNESCAPED_UNICODE);
//} else {
//    echo json_encode($repo::getTranslationsForLines($lines), JSON_UNESCAPED_UNICODE);
//}

echo json_encode($repo::getTranslationsForLines($lines), JSON_UNESCAPED_UNICODE);
