<?php
use Builov\Faust\FaustReader;

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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['show'])) {
    $selected = $_GET['show'];

    $selected = htmlspecialchars($selected, ENT_QUOTES, 'UTF-8');
}

$reader = new FaustReader();

//if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receive'])) { // && $_POST['receive'] == 'config'
//    echo json_encode($reader->getMeta());
//
//    exit;
//}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

$twig = new \Twig\Environment($loader, [
    'cache' => false, /** В разработке кэш лучше выключить */
    'debug' => true,
]);

/**
 * если selected строка, а не массив, она передана get-запросом с кнопки
 */
if (!is_array($selected)) { // вернуть json
    $text = $reader->read($selected);

    echo json_encode($text);

    return;
}

$texts = [];
foreach ($selected as $text_id) {
    $texts[$text_id] = $reader->read($text_id);
}

//print_r($combined); exit;

echo $twig->render('index.html.twig', [
    'title' => 'Фауст',
    'data' => $texts,
    'selected' => array_keys($texts),
    'columns' => count($texts),
    'meta' => $reader->getMeta(),
]);

//https://www.gutenberg.org/ebooks/21000
