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

    /** @param string[] $textIds */
    public function execute(array $textIds): MainPageResponseDTO
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
                $text = $this->repository->getById($id);
                $textDTOs[$id] = TextMapper::toDTO($text, $allMeta[$id]);
            }
        }

        // Преобразуем доменную коллекцию метаданных в простой массив для UI
        $metaData = [];
        foreach ($allMeta as $meta) {
            $metaData[$meta->getId()] = [
                'id' => $meta->getId(),
                'title' => $meta->getTitle(),
                'startsFrom' => $meta->getFragmentStarts()
            ];
        }

        return new MainPageResponseDTO(
            $textDTOs,
            $metaData, //для указанных id
            count($textDTOs)
        );
    }
}
