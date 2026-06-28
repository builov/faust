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
use Builov\Faust\Domain\Repository\TextRepositoryInterface;

class GetMainPageUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private TextMarkupService $markupService
    ) {}

    /** @param string[] $textIds */
    public function execute(array $textIds, int $from = 0, int $count = 50): MainPageResponseDTO
    {
        $allMeta = $this->repository->getAllMeta();
        $textDTOs = [];

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
//                $text = $this->repository->getById($id);
                // 2. Вызываем специализированный метод репозитория для пагинации
                $text = $this->repository->getPaginatedById($id, $from, $count);
                // DTO с разметкой
                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id]);
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
