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
//        print_r($text); exit;

//        $text = $this->filterTextByRange($text, $lineRange->getStart(), $lineRange->getEnd());
        $this->filterTextByRange($text, $lineRange);

//        print_r($text); exit;

        $text = $this->addStyle($text, $meta);

        // фрагменты
        if (!$meta->isComplete()) {
            $text = $this->addTitle($text);

            //todo не надо дополнять пустыми строками, если это единственный текст на странице
            $text = $this->fillWithEmptyLines($text, $meta, $lineRange);
        }

//        print_r($text); exit;
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


    /**
     * Фильтрует структуру TextDTO по диапазону номеров строк, сохраняя вложенность.
     *
     * @param TextDTO $dto Исходный объект DTO
     * @param int $start Начало диапазона
     * @param int $end Конец диапазона
     * @return TextDTO Новый объект со строго отфильтрованной иерархией
     */
    /*function filterTextByRange(TextDTO $dto, int $start, int $end): TextDTO
    {
        $newDto = clone $dto;

        // Фильтруем фрагменты
        $newDto->fragments = array_filter($newDto->fragments, function ($fragment) use ($start, $end) {
            // Фильтруем строки внутри фрагмента
            $fragment->lines = array_filter($fragment->lines, function ($line) use ($start, $end) {
                return $line->number >= $start && $line->number <= $end;
            });

            // Оставляем фрагмент только если в нем остались строки
            return !empty($fragment->lines);
        });

        // Сбрасываем индексы массивов, чтобы они шли от 0, а не сохраняли старые ключи
        $newDto->fragments = array_values($newDto->fragments);
        foreach ($newDto->fragments as $fragment) {
            $fragment->lines = array_values($fragment->lines);
        }

        return $newDto;
    }*/



    private function addTitle(TextDTO $text): TextDTO
    {
        foreach ($text->fragments as $fragment) {
            $fragment->lines[0]->text = '<div class="floating-title scene_title">' . $fragment->title . '</div>' . $fragment->lines[0]->text;
        }

        return $text;
    }

    private function fillWithEmptyLines(TextDTO $text, TextMeta $meta, LineRange $lineRange): TextDTO
    {
        $startIndex = $lineRange->getStart(); // 0 by default
        $endIndex = $lineRange->getEnd(); // PHP_INT_MAX by default

        foreach ($meta->getFragmentStarts() as $fragmentKey => $startLine) { // обход массива 'starts_from'
            if ($startLine > $endIndex) { // $startLine - номер первой строки фрагмента (из конфига)
                continue;
            }

//            $emptyLinesArr = array_fill($startIndex, $startLine - ($startIndex + 1), new TextLineDTO(0, '', ''));
//            $emptyLinesArr = array_fill($startIndex, $startLine - ($startIndex + 1), null);

            //массив с ключами, соотв. номерам строк
            $emptyLinesArr = [];
            if ($startIndex < $startLine) {
                $emptyLinesArr = array_fill($startIndex, $startLine - $startIndex, null);
            }

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
