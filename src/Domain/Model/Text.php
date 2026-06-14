<?php
/**
 * Агрегат, представляющий текст (оригинал или перевод) как упорядоченную коллекцию объектов TextLine.
 * Отвечает за логику сопоставления номеров строк оригинала и перевода.
 */

namespace Builov\Faust\Domain\Model;

class Text
{
    /** @param TextFragment[] $fragments */
    public function __construct(
        private string $id,
        private array  $fragments
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    /** @return TextFragment[] */
    public function getFragments(): array
    {
        return $this->fragments;
    }

    /** @return TextLine[] */
    public function getLines(): array
    {
        $allLines = [];
        $globalIndex = 0;

        foreach ($this->fragments as $fragment) {

//            print_r($fragment->getLines()); exit;

            foreach ($fragment->getLines() as $line) {
                $allLines[] = new TextLine($globalIndex, $line->getText(), $line->getCssClass());
                $globalIndex++;
            }
        }

        return $allLines;
    }

    // Пример доменной логики: проверка наличия строки в переводе
//    public function hasLine(int $lineNumber): bool
//    {
//        return isset($this->lines[$lineNumber]);
//    }

    /**
     * Возвращает массив номеров строк, входящих в строфу для заданной строки
     * @param int $targetLine Номер строки, на которой вызвали меню
     * @return int[] Список номеров строк, составляющих строфу
     */
//    public function getStanzaLineNumbers(int $targetLine): array
//    {
//        // 1. Собираем все номера строк, которые помечены как 'stanza_end'
//        $endLines = [];
//        foreach ($this->lines as $num => $line) {
//            if ($line->getCssClass() === 'stanza_end') {
//                $endLines[] = $num;
//            }
//        }
//        sort($endLines);
//
//        // 2. Ищем границы строфы для нашей targetLine
//        $startLine = 1;
//        $endLine = count($this->lines);
//
//        foreach ($endLines as $index => $currentEnd) {
//            if ($currentEnd >= $targetLine) {
//                $endLine = $currentEnd;
//                $startLine = ($index > 0) ? $endLines[$index - 1] + 1 : 1;
//                break;
//            }
//        }
//
//        // 3. Возвращаем массив номеров строк от старта до конца строфы
//        return range($startLine, $endLine);
//    }
}

