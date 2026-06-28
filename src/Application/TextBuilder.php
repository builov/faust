<?php

namespace Builov\Faust\Application;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextFragment;
use Builov\Faust\Domain\Model\TextLine;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\TextBuilderInterface;
use Builov\Faust\Domain\TextReaderInterface;

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
     * Создает объект Text, содержащий только строки из диапазона [from, from + count]
     */
    public function buildPaginatedText(TextMeta $meta, int $from, int $count): Text
    {
        $textArray = $this->wrapped->readText($meta);

        // 1. Парсим структуру файла целиком (чтобы знать, где какие заголовки и строки)
        $allFragments = $this->parseRawLines($textArray);

        // 2. Нарезаем структуру, сохраняя оригинальные индексы и сквозные номера строк
        $paginatedFragments = $this->sliceFragmentsWithAbsoluteNumbers($allFragments, $meta, $from, $count);

        // 3. Собираем объект Text. Он получит только нужные строки, но с ПРАВИЛЬНЫМИ номерами.
        return $this->assembleFragments($paginatedFragments, $meta);
    }

    private function parseRawLines(array $textArray): array
    {
        $fragments = [];
        $fragmentIndex = 0;
        foreach ($textArray as $line) {
            if ($line === "DELIMITER") {
                $fragmentIndex++;
                continue;
            }
            if (str_starts_with($line, "<title>")) {
                $fragments[$fragmentIndex]['title'] = str_replace(["<title>", "</title>"], '', $line);
                continue;
            }

            $fragments[$fragmentIndex]['lines'][] = ($line === "empty_line") ? "&nbsp;" : $line;
        }
        return $fragments;
    }

    /**
     * Вырезает нужные строки и сразу превращает их в объекты TextLine с абсолютными номерами
     */
    private function sliceFragmentsWithAbsoluteNumbers(array $fragments, TextMeta $config, int $from, int $count): array
    {
        $sliced = [];
        $globalLineIndex = 0; // Сквозной счетчик строк, как они идут в файле
        $remainingCount = $count;

        $fragmentStarts = $config->getFragmentStarts();

        foreach ($fragments as $fIndex => $fragment) {
            $fragmentLines = [];

            // Определяем стартовый номер строки для этого фрагмента из конфига
            // Если конфиг пустой (полный перевод), то считаем от 1
            $startLineNumber = !empty($fragmentStarts) ? ($fragmentStarts[$fIndex] ?? 1) : 1;
            $linesInFragment = $fragment['lines'] ?? [];

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

        return $sliced;
    }

    /**
     * @param TextFragment[] $fragments
     * @return Text
     */
    private function assembleFragments(array $fragments, TextMeta $config): Text
    {
        $textFragments = [];
        $fragmentStarts = $config->getFragmentStarts();

        // фрагменты
        if (!empty($fragmentStarts)) {
            foreach ($fragmentStarts as $fragmentKey => $startLine) {
                // Если при пагинации этот фрагмент не попал в диапазон — пропускаем его
                if (!isset($fragments[$fragmentKey])) {
                    continue;
                }

                /** предполагается соответствие ключей массивов $fragments и $config->getFragmentStarts() */
//                $fragmentLines = [];
//                $lineNumber = null;
//                foreach ($fragments[$fragmentKey]['lines'] as $line) {
//                    if (empty($lineNumber)) {
//                        $lineNumber = $startLine;
//                    }
//
//                    $fragmentLines[$lineNumber] = new TextLine($lineNumber, $line, '');
//
//                    $lineNumber++;
//                }
//
//                $title = $fragments[$fragmentKey]['title'];
//                $textFragments[] = new TextFragment($title, $fragmentLines);

                $fragmentData = $fragments[$fragmentKey];
                $fragmentLines = [];

                foreach ($fragmentData['lines'] as $key => $line) {
                    // Если это сырая строка (из старого метода buildText) — собираем её
                    if (is_string($line)) {
                        if (empty($lineNumber)) {
                            $lineNumber = $startLine;
                        }
                        $fragmentLines[$lineNumber] = new TextLine($lineNumber, $line, '');
                        $lineNumber++;
                    } else {
                        // Если это уже готовый TextLine (из buildPaginatedText) — сохраняем как есть
                        $fragmentLines[$key] = $line;
                    }
                }

                $title = $fragmentData['title'] ?? '';
                $textFragments[] = new TextFragment($title, $fragmentLines);
            }
        }
        // полный перевод
        else {
//            foreach ($fragments[0]['lines'] as $lineNumber => &$line) {
//                $line = new TextLine($lineNumber + 1, $line, '');
//            }
//            unset($line);
//
//            $title = $config->getTitle();
//            $textFragments[] = new TextFragment($title, $fragments[0]['lines']);

            $firstKey = array_key_first($fragments);
            if ($firstKey !== null) {
                $lines = $fragments[$firstKey]['lines'];
                foreach ($lines as $key => &$line) {
                    if (is_string($line)) {
                        $line = new TextLine($key + 1, $line, '');
                    }
                }
                unset($line);

                $title = $config->getTitle();
                $textFragments[] = new TextFragment($title, $lines);
            }
        }

//        print_r($config); exit;

        return new Text($config->getId(), $config->getTitle(), $textFragments);
    }
}