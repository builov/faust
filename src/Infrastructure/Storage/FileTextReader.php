<?php

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\TextReaderInterface;

class FileTextReader implements TextReaderInterface
{
    public function __construct(
        private readonly string $dataDir
    )
    {
    }

    /**
     *
     * @return string[]
     */
    public function readText(TextMeta $meta): array
    {
        $path = $this->dataDir . '/' . $meta->getFilePath();

        if (!file_exists($path)) {
            return [];
        }

        $rawLines = file($path, FILE_IGNORE_NEW_LINES);

        $lines = [];
        foreach ($rawLines as $text) {
            $text = trim($text);
            if ($text === "") {
                continue;
            }
            $lines[] = $text;
        }

        return $lines;
    }
}
