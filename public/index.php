<?php

use Builov\Faust\FaustReader;

require_once __DIR__ . '/../vendor/autoload.php';

$selected = [
    'faust',
    'pasternak',
    'holodkovskiy',
//    'minaev',
//    'shishkov',
//    'griboedov',
//    'nabokov',
//    'zhukovskiy',
//    'balmont',
//    'zhiganets',
];

$reader = new FaustReader();

$texts = [];
foreach ($selected as $text_id) {
    $texts[] = $reader->read($text_id);
}

$combined = array_map(function(...$lines) {
    return $lines;
}, ...$texts);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

$twig = new \Twig\Environment($loader, [
    'cache' => false, /** В разработке кэш лучше выключить */
    'debug' => true,
]);

//print_r($combined); exit;

echo $twig->render('index.html.twig', [
    'title' => 'Фауст',
    'data' => $combined,
    'columns' => count($combined[0]),
]);


//https://www.gutenberg.org/ebooks/21000
