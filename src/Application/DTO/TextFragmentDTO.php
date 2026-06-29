<?php

namespace Builov\Faust\Application\DTO;

class TextFragmentDTO
{
    /** @param TextLineDTO[] $lines */
    public function __construct(
        public readonly string $title,
        public array $lines //todo подумать как вернуть иммутабельность, нарушенную ради возможности добавления пустых строк
    ) {}

    public function addEmptyLinesBefore($emptyLines): void
    {
        $this->lines = array_merge($emptyLines, $this->lines);
    }
}