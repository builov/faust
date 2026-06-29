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
        private string $title,
        private array  $fragments
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
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
}

