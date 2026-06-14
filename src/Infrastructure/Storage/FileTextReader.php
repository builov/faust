<?php

/**
 * парсит текстовые файлы и файлы разметки, собирая из них объекты
 * PoeticLine с CSS-классами.
 *
 * Читает файлы переводов (.txt) и файлы разметки (.css-классы), мёрджит их по номеру строки
 * и собирает сущности TextLine. Заменяет собой старый метод read().
 */

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextFragment;
use Builov\Faust\Domain\Model\TextLine;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\TextReaderInterface;

class FileTextReader implements TextReaderInterface
{
    public function __construct(private string $dataDir)
    {
    }

    /**
     *
     * @return TextLine[]
     */
    public function readText(TextMeta $meta): Text
    {
        $textPath = $meta->getFilePath();
//        $markupPaths = $meta->getMarkupPaths();

        $fullPath = $this->dataDir . '/' . $textPath;

        if (!file_exists($fullPath)) {
            return [];
        }

        // 1. Читаем строки текста
        $rawLines = file($fullPath, FILE_IGNORE_NEW_LINES);

        // объекты TextLine
        $lines = [];
        foreach ($rawLines as $index => $text) {
//            $text = trim($text);
//            if ($text === "") {
//                continue;
//            }
//            $lines[] = $text;

            $lines[] = new TextLine(0, trim($text), '');
        }

//        print_r($lines); exit;

        $defaultFragment = new TextFragment('', $lines);
//        print_r($defaultFragment); exit;

        return new Text($meta->getId(), [$defaultFragment]);
/*
//        print_r($markupPaths); exit;
//        print_r($textLines); exit;

        // Парсинг блоков текста с учетом <title> и DELIMITER
        $textChunks = [];
        $fragmentTitle = '';
        $chunkIndex = 0;

        foreach ($rawLines as $line) {
            $line = trim($line);
            if ($line === "") {
                continue;
            }
            if ($line === "DELIMITER") {
                $textChunks[$chunkIndex][0] = $fragmentTitle . $textChunks[$chunkIndex][0];
                $chunkIndex++;
                continue;
            }
            if (str_starts_with($line, "<title>")) {
                $fragmentTitle = '<div class="floating-title scene_title">' .
                    str_replace(["<title>", "</title>"], '', $line) . '</div>';
                continue;
            }

            $textChunks[$chunkIndex][] = ($line === "empty_line") ? "" : $line;
        }

        // Сборка финального массива строк по логике фрагментов
        $assembledLines = $this->assembleFragments($textChunks, $meta);

//        print_r($assembledLines); exit;

        // 2. Парсим файлы разметки и собираем плоскую карту: [номер_строки => класс]
        $lineNumberToClassMap = [];
        foreach ($markupPaths as $markupPath) {
            if (!file_exists($this->dataDir . '/' . $markupPath)) {
                continue;
            }

            $markupLines = file($this->dataDir . '/' . $markupPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($markupLines as $markupLine) {
                // Разбиваем строку вида: "speech_heading / 38,65,82"
                if (!str_contains($markupLine, '/')) {
                    continue;
                }

                [$cssClass, $rawNumbers] = explode('/', $markupLine, 2);
                $cssClass = trim($cssClass);

                // Извлекаем все номера строк
                $numbers = explode(',', trim($rawNumbers));
                foreach ($numbers as $numberStr) {
                    $num = (int)trim($numberStr);
                    if ($num > 0) {
                        // Присваиваем класс конкретной строке
                        $lineNumberToClassMap[$num] = $cssClass;
                    }
                }
            }
        }

//        print_r($lineNumberToClassMap); exit;

        $lines = [];
        foreach ($assembledLines as $index => $text) {
            $lineNumber = $index + 1; // Номер строки (1-based)

            $cssClass = '';
            if (!empty($text)) {
                $cssClass = $lineNumberToClassMap[$lineNumber] ?? 'default';
            }

            $lines[$index] = new TextLine($index, $text, $cssClass);
        }

        return $lines;
*/
    }

    private function assembleFragments(array $textChunks, TextMeta $config): array
    {
        if (empty($config->getFragmentStarts())) {
            return $textChunks[0] ?? [];
        }

        $result = [];
        $startIndex = 0;

        foreach ($config->getFragmentStarts() as $fragmentKey => $startLine) {
            if (!isset($textChunks[$fragmentKey])) {
                continue;
            }
            $emptyLinesCount = $startLine - ($startIndex + 1);
            if ($emptyLinesCount > 0) {
                $result = array_merge($result, array_fill(0, $emptyLinesCount, ''));
            }
            $result = array_merge($result, $textChunks[$fragmentKey]);
            $startIndex = count($result);
        }

        return $result;
    }
}
