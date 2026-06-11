<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Repository\CacheStorageInterface;

class TranslateLinesUseCase
{
    public function __construct(
        private CacheStorageInterface $cacheStorage
    )
    {
    }

    public function execute(array $lineNumbers, string $mode): array|null
    {
//        print_r($this->cacheStorage->read()); exit;

        $output = [];

        // Переворачиваем массив номеров строк для быстрого поиска по ключам
        $flippedLines = array_flip($lineNumbers);

//        var_dump($flippedLines); exit;

        foreach ($this->cacheStorage->read() as $translationID => $translationLines) {
            // Мгновенно находим пересечения по ключам на уровне ядра PHP
            $foundTexts = array_intersect_key($translationLines, $flippedLines);

            if (!empty($foundTexts)) {
                $output[] = [
                    'label' => (string)$translationID,
                    'texts' => $foundTexts
                ];
            }
        }

//        var_dump($output); exit;

        return empty($output) ? null : $output;
    }
}