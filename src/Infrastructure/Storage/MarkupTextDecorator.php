<?php

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\TextReaderInterface;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextFragment;
use Builov\Faust\Domain\Model\TextLine;

class MarkupTextDecorator implements TextReaderInterface
{
    public function __construct(
        private TextReaderInterface $wrapped,
        private readonly string $dataDir
    ) {}

    public function readText(TextMeta $meta): Text
    {
        $textObject = $this->wrapped->readText($meta);
        $markupPaths = $meta->getMarkupPaths();

        if (empty($markupPaths)) {
            return $textObject;
        }

//        print_r($lines); exit;

        $lineNumberToClassMap = $this->buildMarkupMap($markupPaths);

//        print_r($lineNumberToClassMap); exit;

        $processedFragments = [];
        $globalLineNumber = 1;

        foreach ($textObject->getFragments() as $fragment) {
            $updatedLines = [];

            foreach ($fragment->getLines() as $lineObj) {
                $text = $lineObj->getText();
                $cssClass = 'default';

                if (!empty($text)) {
                    $cssClass = $lineNumberToClassMap[$globalLineNumber] ?? 'default';
                }

                $updatedLines[] = new TextLine($lineObj->getNumber(), $text, $cssClass);
                $globalLineNumber++;
            }

            $processedFragments[] = new TextFragment($fragment->getTitle(), $updatedLines);
        }

        return new Text($textObject->getId(), $processedFragments);
    }

    private function buildMarkupMap(array $markupPaths): array
    {
        $map = [];
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
                        $map[$num] = $cssClass;
                    }
                }
            }
        }
        return $map;
    }
}
