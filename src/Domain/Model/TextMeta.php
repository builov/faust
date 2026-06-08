<?php

/**
 * паспорт перевода. Содержит ID, название, автора и правила сборки фрагментов.
 * Метаданные перевода. Содержит ID, название, тип (полный/частичный)
 * и связи с файлами (в абстрактном виде).
 */

namespace Builov\Faust\Domain\Model;

class TextMeta
{
    public function __construct(
        private string $id,
        private string $title,
        private string $filePath,
        private array $markupPath,
        private array  $fragmentStarts = []
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getMarkupPath(): string
    {
        return $this->markupPath[0];
    }

    public function isFull(): bool
    {
        return empty($this->fragmentStarts);
    }
}
