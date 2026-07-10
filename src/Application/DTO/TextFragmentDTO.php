<?php

namespace Builov\Faust\Application\DTO;

class TextFragmentDTO
{
    /** @param TextLineDTO[] $lines */
    public function __construct(
        public readonly string $title,
        public array $lines //todo подумать как вернуть иммутабельность, нарушенную ради возможности добавления пустых строк
    ) {}

    public function __clone()
    {
        foreach ($this->lines as $key => $line) {
            $this->lines[$key] = clone $line;
        }
    }

    public function getFirstLineNumber() {
        $line  = $this->lines[array_key_first($this->lines)];
        return $line->number;
    }

    public function getLastLineNumber() {
        $line  = end($this->lines);
        return $line->number;
    }

    public function addEmptyLinesBefore($emptyLines): void
    {
        $this->lines = array_merge($emptyLines, $this->lines);
    }
}