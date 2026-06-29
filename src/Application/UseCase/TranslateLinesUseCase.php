<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Repository\CacheStorageInterface;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;

class TranslateLinesUseCase
{
    public function __construct(
        private CacheStorageInterface $cacheStorage,
        private TextRepositoryInterface $repository
    ) {}

    public function getTranslationList(array $lineNumbers): array|null
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
//                    'texts' => $foundTexts,
                    'title' => $this->repository->getMetaById($translationID)->getTitle()
                ];
            }
        }

//        print_r($output); exit;

        return empty($output) ? null : $output;
    }

    public function getTranslation(array $lineNumbers, string $textId): array|null
    {
        $textFull = $this->cacheStorage->read();

        if (!array_key_exists($textId, $textFull)) {
            return null;
        }

        return array_intersect_key($textFull[$textId], array_flip($lineNumbers));
    }
}
