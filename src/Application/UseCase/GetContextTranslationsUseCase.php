<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Infrastructure\Storage\PhpFileCacheStorage;

class GetContextTranslationsUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private PhpFileCacheStorage $cacheStorage
    )
    {
    }

    public function execute(int $targetLineNumber, string $scope): array
    {
        // 1. Получаем оригинал текста, чтобы понять границы строф
        $original = $this->repository->getById('faust');

        // 2. Определяем, какие номера строк нам нужны
        $requiredLines = [$targetLineNumber];
        if ($scope === 'stanza') {
            $requiredLines = $original->getStanzaLineNumbers($targetLineNumber);
        }

        // 3. Быстро ищем тексты строк через оптимизированный кэш trans.php
        $cacheData = $this->cacheStorage->read();
        $allMeta = $this->repository->getAllMeta();
        $result = [];

        foreach ($cacheData as $translationId => $lines) {
            // Пропускаем оригинал в списке предлагаемых переводов
            if ($translationId === 'faust') continue;

            $foundLines = [];
            $hasAllLines = true;

            foreach ($requiredLines as $num) {
                if (isset($lines[$num])) {
                    $foundLines[$num] = $lines[$num];
                } else {
                    // Если перевод частичный и в нем нет этой строки строфы
                    $hasAllLines = false;
                }
            }

            // Если перевод содержит хотя бы одну запрашиваемую строку
            if (!empty($foundLines)) {
                $result[] = [
                    'translation_id' => $translationId,
                    'title' => $allMeta[$translationId]->getTitle(),
                    'is_complete_match' => $hasAllLines, // Полностью ли переведена строфа автором
                    'lines' => $foundLines
                ];
            }
        }

        return $result;
    }
}
