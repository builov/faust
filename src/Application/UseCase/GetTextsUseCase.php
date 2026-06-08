<?php
/**
 * Содержит бизнес-логику: принимает массив ID (или дефолтный список ['faust', 'fet', 'guber']).
 * Запрашивает сущности текстов у репозитория.
 * Проверяет, все ли запрошенные переводы существуют (обработка ошибок).
 */

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Application\DTO\MainPageResponseDTO;
use Builov\Faust\Application\Mapper\TextMapper;

class GetTextsUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository
    )
    {
    }

    /** @param string[] $ids */
    public function execute(array $ids): MainPageResponseDTO
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($ids as $id) {
            if (isset($allMeta[$id])) {
                $poemText = $this->repository->getById($id);
                $textDTOs[$id] = TextMapper::toDTO($poemText, $allMeta[$id]);
            }
        }

        // Преобразуем доменную коллекцию метаданных в простой массив для UI
        $metaData = [];
        foreach ($allMeta as $meta) {
            $metaData[] = [
                'id' => $meta->getId(),
                'title' => $meta->getTitle()
            ];
        }

        return new MainPageResponseDTO(
            $textDTOs,
            $metaData,
            count($textDTOs)
        );
    }
}
