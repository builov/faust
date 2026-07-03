<?php

namespace Builov\Faust\Application;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextFragment;
use Builov\Faust\Domain\Model\TextLine;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\TextBuilderInterface;
use Builov\Faust\Domain\TextReaderInterface;
use Builov\Faust\Domain\VO\LineRange;

class TextBuilder implements TextBuilderInterface
{
    public function __construct(
        private readonly TextReaderInterface $wrapped
    ) {}

    public function buildText(TextMeta $meta): Text
    {
        $textArray = $this->wrapped->readText($meta);

        $fragments = $this->parseRawLines($textArray);

        return $this->assembleFragments($fragments, $meta);
    }

    /**
     * Создает объект Text, содержащий только строки из диапазона LineRange
     */
//    public function buildTextRange(TextMeta $meta, LineRange $lineRange): Text
//    {
//        $text = $this->buildText($meta);
//
//        // Нарезаем структуру, сохраняя оригинальные индексы и номера строк
////        $paginatedFragments = $this->sliceFragments($allFragments, $meta, $lineRange);
//
//
//        return $this->getRange($text, $lineRange);
//    }

//    private function getRange($text, LineRange $lineRange)
//    {
//        print_r($text); exit;
//
//        return $text;
//    }

    private function parseRawLines(array $textArray): array
    {
        // Сдвигаем индексы исходного массива на +1 (ключи начнутся с 1 вместо 0)
        if (!empty($textArray)) {
            $textArray = array_combine(range(1, count($textArray)), $textArray);
        }

        $fragments = [];
        $fragmentIndex = 0;
        foreach ($textArray as $index => $line) {
            if ($line === "DELIMITER") {
                $fragmentIndex++;
                continue;
            }
            if (str_starts_with($line, "<title>")) {
                $fragments[$fragmentIndex]['title'] = str_replace(["<title>", "</title>"], '', $line);
                continue;
            }

            $fragments[$fragmentIndex]['lines'][$index] = ($line === "empty_line") ? "&nbsp;" : $line;
        }

//        print_r($fragments); exit;

        return $fragments;
    }

//    private function sliceFragments(array $fragments, TextMeta $config, LineRange $lineRange): array
//    {
//        return $fragments;
//    }

    /**
     * Вырезает нужные строки и сразу превращает их в объекты TextLine
     * ВНИМАНИЕ! для полных переводов: индексы строк в массиве - 1 based
     *
    private function sliceFragments(array $fragments, TextMeta $config, int $from, int $to): array
    {
        $sliced = [];
        $globalLineIndex = 0; // Сквозной счетчик строк, как они идут в файле
        $remainingCount = ($to - $from) + 1;

        $fragmentStarts = $config->getFragmentStarts();

//        print_r($fragmentStarts); exit;

        foreach ($fragments as $fIndex => $fragment) {
            $fragmentLines = [];

            print_r($fragment);

            // Определяем стартовый номер строки для этого фрагмента из конфига
            // Если конфиг пустой (полный перевод), то считаем от 1 (или с 0?)
            $startLineNumber = !empty($fragmentStarts) ? ($fragmentStarts[$fIndex] ?? 1) : 0;
            $linesInFragment = $fragment['lines'] ?? [];

//            echo $startLineNumber; exit;

            foreach ($linesInFragment as $localIndex => $lineText) {
                // Если строка попадает в запрошенный пользователем диапазон пагинации
                if ($globalLineIndex >= $from && $remainingCount > 0) {

                    // Вычисляем её реальный абсолютный номер в этом фрагменте
                    $absoluteLineNumber = $startLineNumber + $localIndex;

                    // Сразу упаковываем в TextLine (в assembleFragments мы это учтем)
                    $fragmentLines[$absoluteLineNumber] = new TextLine($absoluteLineNumber, $lineText, '');
                    $remainingCount--;
                }
                $globalLineIndex++;
            }

            // Сохраняем фрагмент, только если в него попали строки
            if (!empty($fragmentLines)) {
                $sliced[$fIndex] = [
                    'title' => $fragment['title'] ?? '',
                    'lines' => $fragmentLines // Здесь уже лежат готовые объекты TextLine
                ];
            }
        }

        exit;

        return $sliced;
    }*/

    /**
     * @param TextFragment[] $fragments
     * @return Text
     */
    private function assembleFragments(array $fragments, TextMeta $config): Text
    {
        $textFragments = [];

        // фрагменты
        if (!empty($config->getFragmentStarts())) {
            foreach ($config->getFragmentStarts() as $fragmentKey => $startLine) {

                /** предполагается соответствие ключей массивов $fragments и $config->getFragmentStarts() */
                $fragmentLines = [];
                $lineNumber = null;
                foreach ($fragments[$fragmentKey]['lines'] as $line) {
                    if (empty($lineNumber)) {
                        $lineNumber = $startLine;
                    }

                    $fragmentLines[$lineNumber] = new TextLine($lineNumber, $line, '');

                    $lineNumber++;
                }

                $title = $fragments[$fragmentKey]['title'];
                $textFragments[] = new TextFragment($title, $fragmentLines);
            }
        }
        // полный перевод
        else {

            foreach ($fragments[0]['lines'] as $lineNumber => &$line) {
                $line = new TextLine($lineNumber, $line, '');
            }
            unset($line);

            $title = $config->getTitle();
            $textFragments[] = new TextFragment($title, $fragments[0]['lines']);
        }

//        print_r($config); exit;

        return new Text($config->getId(), $config->getTitle(), $textFragments);
    }

    /**
     * @param TextFragment[] $fragments
     * @return Text
     */
//    private function assembleFragments(array $fragments, TextMeta $config): Text
//    {
//        $textFragments = [];
//        $fragmentStarts = $config->getFragmentStarts();
//
//        // фрагменты
//        if (!empty($fragmentStarts)) {
//            foreach ($fragmentStarts as $fragmentKey => $startLine) {
//                // Если при пагинации этот фрагмент не попал в диапазон — пропускаем его
//                if (!isset($fragments[$fragmentKey])) {
//                    continue;
//                }
//
//
//                $fragmentData = $fragments[$fragmentKey];
//                $fragmentLines = [];
//
//                foreach ($fragmentData['lines'] as $key => $line) {
//                    // Если это сырая строка (из старого метода buildText) — собираем её
//                    if (is_string($line)) {
//                        if (empty($lineNumber)) {
//                            $lineNumber = $startLine;
//                        }
//                        $fragmentLines[$lineNumber] = new TextLine($lineNumber, $line, '');
//                        $lineNumber++;
//                    } else {
//                        // Если это уже готовый TextLine (из buildPaginatedText) — сохраняем как есть
//                        $fragmentLines[$key] = $line;
//                    }
//                }
//
//                $title = $fragmentData['title'] ?? '';
//                $textFragments[] = new TextFragment($title, $fragmentLines);
//            }
//        }
//        // полный перевод
//        else {
//            $firstKey = array_key_first($fragments);
//            if ($firstKey !== null) {
//                $lines = $fragments[$firstKey]['lines'];
//                foreach ($lines as $key => &$line) {
//                    if (is_string($line)) {
//                        $line = new TextLine($key + 1, $line, '');
//                    }
//                }
//                unset($line);
//
//                $title = $config->getTitle();
//                $textFragments[] = new TextFragment($title, $lines);
//            }
//        }
//
////        print_r($config); exit;
//
//        return new Text($config->getId(), $config->getTitle(), $textFragments);
//    }
}