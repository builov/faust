<?php

namespace Builov\Faust\Domain;

class TextMarkuper
{
    public function applyMarkup(array $lines, array $markupRules): array
    {
        $parsedMarkup = [];
        foreach ($markupRules as $line) {
            $parts = explode(' / ', trim($line));
            if (isset($parts[1])) {
                foreach (explode(',', $parts[1]) as $lineNumber) {
                    $parsedMarkup[trim($lineNumber)] = trim($parts[0]);
                }
            }
        }

        $result = [];
        foreach ($lines as $index => $value) {
            $lineNumber = $index + 1;
            $style = $parsedMarkup[$lineNumber] ?? 'default';
            $result[] = [$value, $style];
        }

        return $result;
    }
}
