<?php

/**
 * Объект строки. Содержит номер строки (ID), исходный текст и CSS-класс для визуализации.
 */

namespace Builov\Faust\Domain\Model;

class TextLine
{
    public function __construct(
        private int $number,
        private string $text,
        private string $cssClass
    ) {}

    public function getNumber(): int { return $this->number; }
    public function getText(): string { return $this->text; }
    public function getCssClass(): string { return $this->cssClass; }
}
