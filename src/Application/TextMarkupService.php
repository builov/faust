<?php

namespace Builov\Faust\Application;

use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\VO\LineRange;

class TextMarkupService
{
    public function __construct(
        private readonly string $dataDir
    ) {}


    public function applyMarkup(TextDTO $text, TextMeta $meta, LineRange $lineRange): TextDTO
    {
        $this->filterTextByRange($text, $lineRange);

        $text = $this->addStyle($text, $meta);

        // фрагменты
        if (!$meta->isComplete()) {
            $text = $this->addTitle($text);

            if (!empty($text->fragments)) { //вызывается если фрагмент попадает в диапазон $lineRange
                $text = $this->fillWithEmptyLines($text, $meta, $lineRange);
            }
        }

        return $text;
    }

    /**
     * Модифицирует исходный объект, удаляя строки вне диапазона.
     */
    function filterTextByRange(TextDTO $text, LineRange $lineRange): void
    {
        $start = $lineRange->getStart();
        $end = $lineRange->getEnd();

        // Фильтруем фрагменты прямо в исходном объекте
        $text->fragments = array_filter($text->fragments, function ($fragment) use ($start, $end) {

            // Фильтруем строки прямо внутри фрагмента
            $fragment->lines = array_filter($fragment->lines, function ($line) use ($start, $end) {
                return $line->number >= $start && $line->number <= $end;
            });

            $fragment->lines = array_values($fragment->lines);

            // Если во фрагменте не осталось строк, array_filter удалит сам фрагмент
            return !empty($fragment->lines);
        });

        // Переиндексируем массив фрагментов
        $text->fragments = array_values($text->fragments);
    }

    private function addTitle(TextDTO $text): TextDTO
    {
        foreach ($text->fragments as $fragment) {
            $fragment->lines[0]->text = '<div class="floating-title scene_title">' . $fragment->title . '</div>' . $fragment->lines[0]->text;
        }

        return $text;
    }

    private function fillWithEmptyLines(TextDTO $text, TextMeta $meta, LineRange $lineRange): TextDTO
    {
        $rangeStartLine = max(1, $lineRange->getStart()); // 0 by default, для корректного добавления пустых строк минимальный $rangeStartLine = 1
        $rangeEndLine = $lineRange->getEnd(); // PHP_INT_MAX by default

//        echo '$rangeStartLine: ' . $rangeStartLine . PHP_EOL;
//        echo '$rangeEndLine: ' . $rangeEndLine . PHP_EOL;

        foreach ($text->fragments as $fragment) {
//            echo $fragment->getFirstLineNumber() . PHP_EOL;
//            echo $fragment->getLastLineNumber() . PHP_EOL;

            $fragmentStartLine = $fragment->getFirstLineNumber();

            if ($fragmentStartLine > $rangeEndLine) { // $fragmentStartLine - номер первой строки фрагмента (из конфига)
                continue;
            }

//            echo '$fragment->lines BEFORE: ' . count($fragment->lines) . PHP_EOL;

            $emptyLinesArr = [];
            if ($fragmentStartLine > $rangeStartLine) {

                if (!empty($previousFragmentEndLine)) {

                    $emptyLinesArr = array_fill($previousFragmentEndLine + 1, $fragmentStartLine - ($previousFragmentEndLine + 1), null);

//                    echo '$previousFragmentEndLine: ' . $previousFragmentEndLine . PHP_EOL;
//                    echo '$fragmentStartLine: ' . $fragmentStartLine . PHP_EOL;
//                    echo '$emptyLinesArr: ' . count($emptyLinesArr) . PHP_EOL;
                } else {
                    $emptyLinesArr = array_fill($rangeStartLine, $fragmentStartLine - $rangeStartLine, null);

//                    echo '$previousFragmentEndLine: 1' . PHP_EOL;
//                    echo '$fragmentStartLine: ' . $fragmentStartLine . PHP_EOL;
//                    echo '$emptyLinesArr: ' . count($emptyLinesArr) . PHP_EOL;
                }
            }

            $fragment->addEmptyLinesBefore($emptyLinesArr);

//            echo '$fragment->lines AFTER: ' . count($fragment->lines) . PHP_EOL;

            $previousFragmentEndLine = $fragment->getLastLineNumber();
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
