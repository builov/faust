<?php
/**
 * паспорт текста. Содержит ID, название, тип (полный/частичный), ссылки на файлы и правила сборки фрагментов.
 */

namespace Builov\Faust\Domain\Model;

class TextMeta
{
    /**
     * @param string $id
     * @param string $title
     * @param string $filePath
     * @param string[] $markupPaths Массив путей к файлам разметки
     * @param int[] $fragmentStarts
     */
    public function __construct(
        private string $id,
        private string $title,
        private string $filePath,
        private array $markupPaths,
        private array $fragmentStarts = []
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

    /** @return string[] */
    public function getMarkupPaths(): array
    {
        return $this->markupPaths;
    }

    /** @return int[] */
    public function getFragmentStarts(): array
    {
        return $this->fragmentStarts;
    }

    public function isComplete(): bool
    {
        return empty($this->fragmentStarts);
    }
}
