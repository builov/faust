<?php

use Builov\Faust\FaustReader;

require_once __DIR__ . '/../vendor/autoload.php';

$selected = [
    'faust',
    'holodkovskiy'
];

$reader = new FaustReader();

$texts = [];
foreach ($selected as $text_id) {
    $text = $reader->read($text_id);

    $markup = [];
    foreach ($reader->read('faust_markup') as $line) {
        $type = explode(' / ', $line);

        foreach (explode(',', $type[1]) as $lineNumber) {
            $markup[$lineNumber] = $type[0];
        }
    }

    $textMarkedUp = array_map(function($key, $value) use ($markup) {
        if (isset($markup[$key+1])) {
            return [$value, $markup[$key+1]];
        } else {
            return [$value, 'default'];
        }
    }, array_keys($text), $text);

    $texts[] = $textMarkedUp;
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
