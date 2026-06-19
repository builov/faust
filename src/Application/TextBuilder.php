<?php

namespace Builov\Faust\Application;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextBuilderInterface;
use Builov\Faust\Domain\Model\TextLine;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\TextReaderInterface;
use Builov\Faust\Domain\Model\TextFragment;

class TextBuilder implements TextBuilderInterface
{
    public function __construct(
        private readonly TextReaderInterface $wrapped
    ) {}

    public function buildText(TextMeta $meta): Text
    {
        $textArray = $this->wrapped->readText($meta);

        $fragments = [];
        $fragmentIndex = 0;
        foreach ($textArray as $line) {
            /**
             * строка "DELIMITER" должна идти после фрагмента,
             * тогда сохраненный на одной из предыдущих итераций цикла заголовок <title>
             * добавляется к первой строке фрагмента
             */
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

        return $this->assembleFragments($fragments, $meta);
    }

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

                //вариант без создания временного массива
//            for ($i = count($fragments[$fragmentKey]['lines']) - 1; $i >= 0; $i--) {
//                $fragments[$fragmentKey]['lines'][$startLine + $i] = $fragments[$fragmentKey]['lines'][$i];
//                unset($fragments[$fragmentKey]['lines'][$i]);
//            }

                $title = $fragments[$fragmentKey]['title'];
                $textFragments[] = new TextFragment($title, $fragmentLines);
            }
        }
        // полный перевод
        else {

            foreach ($fragments[0]['lines'] as $lineNumber => &$line) {
                $line = new TextLine($lineNumber + 1, $line, '');
            }
            unset($line);

            $title = $config->getTitle();
            $textFragments[] = new TextFragment($title, $fragments[0]['lines']);
        }

//        print_r($config); exit;

        return new Text($config->getId(), $config->getTitle(), $textFragments);
    }
}