<?php
//use Builov\Faust\FaustReader;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

$selected = [
    'faust',
//    'translate',
//    'pavlov',
//    'pasternak',
//    'holodkovskiy',
    'fet',
//    'ivanov'
//    'zertelev'
//    'trunin'
//    'grekov'
//    'strugovshikov'
//    'vronchenko'
//    'maklezova'
//    'golovanov'
//    'vrangel'
//    'mamontov'
//    'brusov'
    'guber'
//    'minaev',
//    'shishkov',
//    'griboedov',
//    'nabokov',
//    'zhukovskiy',
//    'b-kiy',
//    'balmont',
//    'zhiganets',
//    'tutchev'
//    'bek'
//'kaftyrev'
//'venevitinov'
//'merezhkovskiy'
//'hitrova'
//    'ogarev'
//'berg'
//'lihonin'
//'turgenev'
//    'zagorskiy'
//    'aksakov',
//    'sempervero'
//'mihaylov'
//'krasov'
//'barykova'
];

//Сделай подстрочный перевод на русский без немецкого оригинала, построчный, максимально близкий к структуре и порядку слов.
//https://www.gutenberg.org/ebooks/21000

// --- Инициализация инфраструктуры и сервисов (DI) ---
$configRepository = new \Builov\Faust\Infrastructure\JsonConfigRepository(DATA_DIR);
$fileReader = new \Builov\Faust\Infrastructure\FileReader(DATA_DIR);
$linesRepository = new \Builov\Faust\Infrastructure\LinesRepository(DATA_DIR);

$textMarkuper = new \Builov\Faust\Domain\TextMarkuper();

$reader = new \Builov\Faust\Application\FaustReaderService(
    $configRepository,
    $fileReader,
    $textMarkuper,
    $linesRepository
);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // В разработке кэш лучше выключить
    'debug' => true,
]);

//$reader = new FaustReader(); //старая версия

// --- Обработка AJAX / GET запросов ---

// Получение параллельных переводов для строк (например, через JS fetch: /index.php?lines=5,12)
//if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['lines'])) {
//    $lineNumbers = explode(',', $_GET['lines']);
//    $translations = $linesRepository->getTranslationsForLines($lineNumbers);
//
//    header('Content-Type: application/json; charset=utf-8');
//    echo json_encode($translations);
//    return;
//}

// Запуск переиндексации (например, /index.php?action=reindex)
//if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'reindex') {
//    $reader->buildIndex(DATA_DIR);
//    header('Content-Type: application/json; charset=utf-8');
//    echo json_encode(['status' => 'success', 'message' => 'trans.php успешно обновлен']);
//    return;
//}

// Стандартная обработка отображения одного текста
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['show'])) {
    $selected = $_GET['show'];

    $selected = htmlspecialchars($selected, ENT_QUOTES, 'UTF-8');
}

// если selected строка, а не массив, она передана get-запросом с кнопки
if (!is_array($selected)) { // вернуть json
    $text = $reader->read($selected);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($text);
    return;
}

// --- Основной рендеринг страницы Twig ---

$texts = [];
foreach ($selected as $text_id) {
    $texts[$text_id] = $reader->read($text_id);
}

echo $twig->render('index.html.twig', [
    'title' => 'Фауст',
    'data' => $texts,
    'selected' => array_keys($texts),
    'columns' => count($texts),
    'meta' => $reader->getMeta(),
]);
