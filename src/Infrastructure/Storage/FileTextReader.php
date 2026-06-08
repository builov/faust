<?php

/**
 * парсит текстовые файлы и файлы разметки, собирая из них объекты
 * PoeticLine с CSS-классами.
 *
 * Читает файлы переводов (.txt) и файлы разметки (.css-классы), мёрджит их по номеру строки
 * и собирает сущности TextLine. Заменяет собой старый метод read().
 */

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\TextLine;

class FileTextReader
{
    /** @return TextLine[] */
    public function readLines(string $textPath, string $markupPath): array
    {
        // Читаем строки текста
        $textLines = file($textPath, FILE_IGNORE_NEW_LINES);
        // Читаем классы разметки
        $markupLines = file($markupPath, FILE_IGNORE_NEW_LINES);

        $lines = [];
        foreach ($textLines as $index => $text) {
            $lineNumber = $index + 1; // Номер строки (1-based)
            $cssClass = $markupLines[$index] ?? 'default-class';

            $lines[$lineNumber] = new TextLine($lineNumber, $text, $cssClass);
        }

        return $lines;
    }
}
