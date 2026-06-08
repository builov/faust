<?php

/**
 * текст перевода (или оригинала). Хранит массив строк или фрагментов.
 * Знает, полный он или частичный.
 *
 * Синхронизация строк: За точное совпадение номеров строк оригинала и перевода
 * теперь отвечает доменная модель TranslationText. Она гарантирует,
 * что строка №5 перевода всегда встанет напротив строки №5 оригинала.
 *
 * Агрегат, представляющий текст (оригинал или перевод) как упорядоченную коллекцию объектов TextLine.
 * Отвечает за логику сопоставления номеров строк оригинала и перевода.
 */

namespace Builov\Faust\Domain\Model;

class Text
{
    /** @param TextLine[] $lines */
    public function __construct(
        private string $id,
        private array $lines
    ) {}

    public function getId(): string { return $this->id; }

    /** @return TextLine[] */
    public function getLines(): array { return $this->lines; }

    // Пример доменной логики: проверка наличия строки в переводе
    public function hasLine(int $lineNumber): bool
    {
        return isset($this->lines[$lineNumber]);
    }
}
