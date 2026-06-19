<?php
/**
 * Запрашивает сущности текстов из репозитория.
 * Проверяет, все ли запрошенные переводы существуют (обработка ошибок).
 */

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Application\DTO\MetaDataDTO;
use Builov\Faust\Application\TextMarkupService;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Application\DTO\MainPageResponseDTO;
use Builov\Faust\Application\Mapper\TextMapper;

class GetMainPageUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private TextMarkupService $markupService
    ) {}

    /** @param string[] $textIds */
    public function execute(array $textIds): MainPageResponseDTO
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
                $text = $this->repository->getById($id);
                // DTO с разметкой
                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id]);
                // без разметки
//                $textDTOs[] = TextMapper::toDTO($text);
            }
        }

//        print_r($textDTOs); exit;

//        $markedUp = [];
//        foreach ($textDTOs as $text) {
//            if (isset($allMeta[$id])) {
//                $markedUp[] = $this->markupService->applyMarkup($text, $allMeta[$id]);
//            }
//        }
//        print_r($markedUp); exit;

        // Преобразуем доменную коллекцию метаданных в простой массив для UI
        $metaData = [];
        foreach ($allMeta as $meta) {
            $id = $meta->getId();

            $metaData[$id] = new MetaDataDTO($id, $meta->getTitle(), $meta->getFragmentStarts());
        }

//        print_r($metaData); exit;

        return new MainPageResponseDTO(
            $textDTOs,
            $metaData, //для всех текстов
            count($textDTOs)
        );
    }
}
