<?php

namespace Builov\Faust\Application\TextDecorator;

use Builov\Faust\Domain\Model\TextReaderInterface;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextFragment;
use Builov\Faust\Domain\Model\TextLine;

class HtmlTextDecorator implements TextReaderInterface
{
    public function __construct(private TextReaderInterface $wrapped) {}

    public function readText(TextMeta $meta): Text
    {
        // Получаем строки от предыдущего ридера
        $textObject = $this->wrapped->readText($meta);
        $initialLines = $textObject->getLines();

//        print_r($textObject); exit;

        // TODO: Перенесите сюда ваш код с foreach ($rawLines),
        // парсингом <title>, DELIMITER и вызовом assembleFragments.
        // На выходе формируйте новый массив TextLine[].

        // Парсинг блоков текста с учетом <title> и DELIMITER
//        $textChunks = [];
//        $fragmentTitle = '';
//        $chunkIndex = 0;
        $fragments = [];
        $currentFragmentTitle = '';
        $currentFragmentLines = [];
        $localIndex = 0;

        foreach ($initialLines as $lineObj) {

            $text = $lineObj->getText();

            if ($text === "") {
                continue;
            }

            if ($text === "DELIMITER") {
                $fragments[] = new TextFragment($currentFragmentTitle, $currentFragmentLines);
                $currentFragmentLines = [];
                $currentFragmentTitle = '';
                $localIndex = 0;
                continue;
            }
            if (str_starts_with($text, "<title>")) {
                $currentFragmentTitle = str_replace(["<title>", "</title>"], '', $text);
                continue;
            }

            $cleanText = ($text === "empty_line") ? "" : $text;
            $currentFragmentLines[] = new TextLine($localIndex, $cleanText, 'default');
            $localIndex++;

            /*
             * строка "DELIMITER" должна идти после фрагмента,
             * тогда сохраненный на одной из предыдущих итераций цикла заголовок <title>
             * добавляется к первой строке фрагмента
             */
//            if ($line === "DELIMITER") {
//                $textChunks[$chunkIndex][0] = $fragmentTitle . $textChunks[$chunkIndex][0];
//                $chunkIndex++;
//                continue;
//            }
//            if (str_starts_with($line, "<title>")) {
//                $fragmentTitle = '<div class="floating-title scene_title">' .
//                    str_replace(["<title>", "</title>"], '', $line) . '</div>';
//                continue;
//            }
//
//            $textChunks[$chunkIndex][] = ($line === "empty_line") ? "&nbsp;" : $line;
        }

        if (!empty($currentFragmentLines) || $currentFragmentTitle !== '') {
            $fragments[] = new TextFragment($currentFragmentTitle, $currentFragmentLines);
        }

        print_r($fragments); exit;

        $assembledFragments = $this->assembleFragments($fragments, $meta);

        return new Text($textObject->getId(), $assembledFragments);

        // Сборка финального массива строк по логике фрагментов
//        $assembledLines = $this->assembleFragments($textChunks, $meta);
//        print_r($assembledLines); exit;
//        return $assembledLines;
    }

    /**
     * @param TextFragment[] $fragments
     * @return TextFragment[]
     */
    private function assembleFragments(array $fragments, TextMeta $config): array
    {
        if (empty($config->getFragmentStarts())) {
            return $fragments;
        }

        $result = [];
        $startIndex = 0;

        foreach ($config->getFragmentStarts() as $fragmentKey => $startLine) {
            if (!isset($fragments[$fragmentKey])) {
                continue;
            }

            $fragment = $fragments[$fragmentKey];
            $emptyLinesCount = $startLine - ($startIndex + 1);

            $fragment->padLeft($emptyLinesCount);
            $result[] = $fragment;

            $startIndex += count($fragment->getLines());
        }

        return $result;
    }
}
