<?php

namespace Builov\Faust\Application\TextDecorator;

use Builov\Faust\Domain\Model\TextReaderInterface;
use Builov\Faust\Domain\Model\TextMeta;

class HtmlTextDecorator implements TextReaderInterface
{
    public function __construct(private TextReaderInterface $wrapped) {}

    public function readLines(TextMeta $meta): array
    {
        // Получаем строки от предыдущего ридера
        $lines = $this->wrapped->readLines($meta);

        // TODO: Перенесите сюда ваш код с foreach ($rawLines),
        // парсингом <title>, DELIMITER и вызовом assembleFragments.
        // На выходе формируйте новый массив TextLine[].

        // Парсинг блоков текста с учетом <title> и DELIMITER
        $textChunks = [];
        $fragmentTitle = '';
        $chunkIndex = 0;

        foreach ($lines as $line) {

            /*
             * строка "DELIMITER" должна идти после фрагмента,
             * тогда сохраненный на одной из предыдущих итераций цикла заголовок <title>
             * добавляется к первой строке фрагмента
             */
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

            $textChunks[$chunkIndex][] = ($line === "empty_line") ? "&nbsp;" : $line;
        }

        // Сборка финального массива строк по логике фрагментов
        $assembledLines = $this->assembleFragments($textChunks, $meta);

//        print_r($assembledLines); exit;

        return $assembledLines;
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
