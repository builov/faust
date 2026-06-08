<?php

namespace Builov\Faust\Infrastructure;

class FileReader
{
    public function __construct(private string $dataDir) {}

    public function readLines(string $relativeInfix): array
    {
        $fullPath = $this->dataDir . $relativeInfix;
        if (!file_exists($fullPath)) {
            return [];
        }
        return file($fullPath, FILE_IGNORE_NEW_LINES) ?: [];
    }
}
