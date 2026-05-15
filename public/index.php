<?php

use Builov\Faust\FaustReader;

require_once __DIR__ . '/../vendor/autoload.php';

$src = [
    'original',
    'pasternak',
    'holodkovskiy',
    'minaev',
    'shishkov',
    'griboedov',
    'nabokov',
    'zhukovskiy',
    'balmont',
    'zhiganets',
];

$selected = [
    'faust',
    'pavlov',
//    'pasternak',
//    'holodkovskiy',
//    'minaev',
//    'shishkov',
//    'griboedov',
//    'nabokov',
//    'zhukovskiy',
//    'b-kiy',
//    'balmont',
//    'zhiganets',
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['show'])) {
    $selected = $_GET['show'];

    $selected = htmlspecialchars($selected, ENT_QUOTES, 'UTF-8');
}

$reader = new FaustReader();

$buttons = $reader->getButtons();

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

$twig = new \Twig\Environment($loader, [
    'cache' => false, /** В разработке кэш лучше выключить */
    'debug' => true,
]);

if (!is_array($selected)) { // вернуть json
    $text = $reader->read($selected);

    echo json_encode($text);

    return;
}

$texts = [];
foreach ($selected as $text_id) {
    $texts[$text_id] = $reader->read($text_id);
}

//$combined = array_map(function (...$lines) {
//    return $lines;
//}, ...$texts);

$combined = $texts;

//print_r($combined); exit;

echo $twig->render('index.html.twig', [
    'title' => 'Фауст',
    'data' => $combined,
//    'columns' => count($combined[0]),
    'columns' => count($texts),
    'buttons' => $buttons,
]);


//https://www.gutenberg.org/ebooks/21000
