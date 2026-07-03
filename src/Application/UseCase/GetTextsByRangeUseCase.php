<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Application\DTO\MainPageResponseDTO;
use Builov\Faust\Application\DTO\MetaDataDTO;
use Builov\Faust\Application\TextMapper;
use Builov\Faust\Application\TextMarkupService;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\VO\LineRange;

class GetTextsByRangeUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private TextMarkupService $markupService
    ) {}

    /** @param string[] $textIds */
    public function execute(array $textIds, LineRange $lineRange): array
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
                $text = $this->repository->getById($id);

                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);
            }
        }

        return $textDTOs;

        // Преобразуем доменную коллекцию метаданных в простой массив для UI
//        $metaData = [];
//        foreach ($allMeta as $meta) {
//            $id = $meta->getId();
//
//            $metaData[$id] = new MetaDataDTO($id, $meta->getTitle(), $meta->getFragmentStarts());
//        }

//        print_r($metaData); exit;

//        return new MainPageResponseDTO(
//            $textDTOs,
//            $metaData, //для всех текстов
//            count($textDTOs)
//        );
    }
}