<?php

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\TextReaderInterface;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\TextLine;

class MarkupTextDecorator implements TextReaderInterface
{
    public function __construct(
        private TextReaderInterface $wrapped,
        private readonly string $dataDir
    ) {}

    public function readLines(TextMeta $meta): array
    {
        $lines = $this->wrapped->readLines($meta);
        $markupPaths = $meta->getMarkupPaths();

        if (empty($markupPaths)) {
            return $lines;
        }

//        print_r($lines); exit;

        $lineNumberToClassMap = [];
        foreach ($markupPaths as $markupPath) {
            $fullPath = $this->dataDir . '/' . $markupPath;
            if (!file_exists($fullPath)) {
                continue;
            }

            $markupLines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($markupLines as $markupLine) {
                if (!str_contains($markupLine, '/')) {
                    continue;
                }

                [$cssClass, $rawNumbers] = explode('/', $markupLine, 2);
                $cssClass = trim($cssClass);

                $numbers = explode(',', trim($rawNumbers));
                foreach ($numbers as $numberStr) {
                    $num = (int)trim($numberStr);
                    if ($num > 0) {
                        $lineNumberToClassMap[$num] = $cssClass;
                    }
                }
            }
        }

//        print_r($lineNumberToClassMap); exit;

        $processedLines = [];
        foreach ($lines as $index => $text) {
            $lineNumber = $index + 1;
//            $text = $lineObj->getText();

            $cssClass = 'default';
            if (!empty($text)) {
                $cssClass = $lineNumberToClassMap[$lineNumber] ?? 'default';
            }

            // создание объекта TextLine
            $processedLines[] = new TextLine($index, $text, $cssClass);
        }

        return $processedLines;
    }
}
