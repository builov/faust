<?php

namespace Builov\Faust\Domain\Model;

class TextFragment
{
    /**
     * @param string $title
     * @param TextLine[] $lines
     */
    public function __construct(
        private string $title,
        private array  $lines
    )
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return TextLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}
