<?php

namespace Builov\Faust\Domain\Model;

class TextFragment
{
    /**
     * @param string $title Чистый заголовок (без HTML-тегов)
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

    /**
     * Дополнение фрагмента пустыми строками слева (выравнивание)
     */
    public function padLeft(int $emptyLinesCount): void
    {
        if ($emptyLinesCount <= 0) {
            return;
        }

        $padding = [];
        for ($i = 0; $i < $emptyLinesCount; $i++) {
            $padding[] = new TextLine(0, '', 'default');
        }

        $this->lines = array_merge($padding, $this->lines);
    }
}
