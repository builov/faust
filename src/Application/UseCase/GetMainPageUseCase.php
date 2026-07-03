<?php
/**
 * Запрашивает сущности текстов из репозитория.
 * Проверяет, все ли запрошенные переводы существуют (обработка ошибок).
 */

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Application\DTO\MainPageResponseDTO;
use Builov\Faust\Application\DTO\MetaDataDTO;
use Builov\Faust\Application\TextMapper;
use Builov\Faust\Application\TextMarkupService;
use Builov\Faust\Domain\Repository\CacheStorageInterface;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\VO\LineRange;

class GetMainPageUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private TextMarkupService $markupService,
        private CacheStorageInterface $cacheStorage
    ) {}

    /** @param string[] $textIds */
    public function execute(array $textIds, LineRange $lineRange): MainPageResponseDTO
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
//                if ($lineRange->isAll()) {
//                    $text = $this->repository->getById($id, $lineRange);
//
//                    $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);
//                } else {
//                    $text = $this->repository->getRangeById($id, $lineRange);
//
//                    $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);
//                }

                $text = $this->repository->getById($id);

                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);

                // DTO с разметкой
//                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id]);
                // без разметки
//                $textDTOs[] = TextMapper::toDTO($text);
            }
        }

//        print_r($textDTOs); exit;

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
