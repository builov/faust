<?php

namespace Builov\Faust\Application;

use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Application\DTO\TextLineDTO;
use Builov\Faust\Domain\Model\TextLine;
use Builov\Faust\Domain\Model\TextMeta;

class TextMarkupService
{
    public function __construct(
        private readonly string $dataDir
    ) {}


    public function applyMarkup(TextDTO $text, TextMeta $meta): TextDTO
    {
        $text = $this->addStyle($text, $meta);

        // фрагменты
        if (!$meta->isComplete()) {
            $text = $this->addTitle($text);

            $text = $this->fillWithEmptyLines($text, $meta);
        }

//        print_r($text); exit;
        return $text;
    }

    private function addTitle(TextDTO $text): TextDTO
    {
        foreach ($text->fragments as $fragment) {
            $fragment->lines[0]->text = '<div class="floating-title scene_title">' . $fragment->title . '</div>' . $fragment->lines[0]->text;
        }

        return $text;
    }

    private function fillWithEmptyLines(TextDTO $text, TextMeta $meta): TextDTO
    {
        $startIndex = 0;

        foreach ($meta->getFragmentStarts() as $fragmentKey => $startLine) { // обход массива 'starts_from'
//            $emptyLinesArr = array_fill($startIndex, $startLine - ($startIndex + 1), new TextLineDTO(0, '', ''));
            //массив с ключами, соотв. номерам строк
            $emptyLinesArr = array_fill($startIndex, $startLine - ($startIndex + 1), null);
            //просто кол-во пустых строк
//            $emptyLinesQuantity = $startLine - ($startIndex + 1);

            $text->fragments[$fragmentKey]->addEmptyLinesBefore($emptyLinesArr); //индекс в 'starts_from' ($fragmentKey) должен соответствовать индексу фрагмента в тексте

            $startIndex += count($text->fragments[$fragmentKey]->lines);
        }

        return $text;
    }

    private function addStyle(TextDTO $text, TextMeta $meta): TextDTO
    {
        $markupPaths = $meta->getMarkupPaths();

        $classMap = $this->buildMarkupMap($markupPaths);

        foreach ($text->fragments as $fragment) {
            foreach ($fragment->lines as $line) {
                $line->semantics = $classMap[$line->number] ?? 'default';
            }
        }

        return $text;
    }

    private function buildMarkupMap(array $markupPaths): array
    {
        $map = [];
        foreach ($markupPaths as $markupPath) {
            $fullPath = $this->dataDir . '/' . $markupPath;
            if (!file_exists($fullPath)) {
                continue;
            }

            $markupLines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($markupLines as $markupLine) {
                if (!str_contains($markupLine, '/')) {
                    continue;
                }

                [$cssClass, $rawNumbers] = explode('/', $markupLine, 2);
                $cssClass = trim($cssClass);

                $numbers = explode(',', trim($rawNumbers));
                foreach ($numbers as $numberStr) {
                    $num = (int)trim($numberStr);
                    if ($num > 0) {
                        $map[$num] = $cssClass;
                    }
                }
            }
        }
        return $map;
    }
}
