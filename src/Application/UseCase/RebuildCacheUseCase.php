<?php

/**
 * запускает процесс пересборки быстрого индекса (замена старого index() и reload()).
 *
 * Заменяет старый метод index(). Дает команду репозиторию агрегировать все строки и обновить оптимизированное хранилище (кэш-файл).
 */

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Infrastructure\Storage\PhpFileCacheStorage;

class RebuildCacheUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private PhpFileCacheStorage $cacheStorage
    ) {}

    public function execute(): void
    {
        $allMeta = $this->repository->getAllMeta();
        $cacheData = [];

        // Собираем двухуровневый массив строк: [id_перевода => [номер_строки => текст]]
        foreach ($allMeta as $id => $meta) {
            $poemText = $this->repository->getById($id);

            foreach ($poemText->getLines() as $num => $line) {
                // Сохраняем только непустые строки стихотворного текста
                if (trim($line->getText()) !== '') {
                    $cacheData[$id][$num] = $line->getText();
                }
            }
        }

        // Записываем собранный массив в trans.php
        $this->cacheStorage->write($cacheData);
    }
}

