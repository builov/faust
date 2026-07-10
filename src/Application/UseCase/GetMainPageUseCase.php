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
        $maxTextLength = 0;

        foreach ($textIds as $id) {
            if (isset($allMeta[$id])) {
                $text = $this->repository->getById($id);

                $maxTextLength = max($text->getLinesCount(), $maxTextLength);

                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);

                // DTO с разметкой
//                $textDTOs[$id] = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id]);
                // без разметки
//                $textDTOs[] = TextMapper::toDTO($text);
            }
        }

//        print_r($maxTextLength); exit;

        // Преобразуем доменную коллекцию метаданных в простой массив для UI
        $metaData = [];
        foreach ($allMeta as $meta) {
            $id = $meta->getId();

            $metaData[$id] = new MetaDataDTO($id, $meta->getTitle(), $meta->getFragmentStarts());
        }

//        print_r($metaData); exit;

        $navLinks = [];

        $navLinks['table_of_contents'] = [
            ['1-35','Посвящение'],
            ['36-258','Пролог в театре'],
            ['259-394','Пролог на небесах'],
            ['395-897','Сцена 1. Ночь'],
            ['898-1329','Сцена 2. За городскими воротами'],
            ['1330-1723','Сцена 3. Кабинет Фауста'],
            ['1724-2372','Сцена 4. Кабинет Фауста'],
            ['2373-2795','Сцена 5. Погреб Ауэрбаха в Лейпциге'],
            ['2796-3156','Сцена 6. Кухня ведьмы'],
            ['3157-3261','Сцена 7. Улица'],
            ['3262-3415','Сцена 8. Вечер'],
            ['3416-3494','Сцена 9. Гулянье'],
            ['3495-3731','Сцена 10. Дом соседки'],
            ['3732-3801','Сцена 11. Улица'],
            ['3802-4011','Сцена 12. Сад Марты'],
            ['4012-4053','Сцена 13. Беседка'],
            ['4054-4234','Сцена 14. Лес и пещера'],
            ['4235-4277','Сцена 15. Комната Гретхен'],
            ['4278-4463','Сцена 16. Сад Марты'],
            ['4464-4529','Сцена 17. У колодца'],
            ['4530-4566','Сцена 18. У городской стены'],
            ['4567-4771','Сцена 19. Hочь. Улица перед домом Гретхен'],
            ['4772-4847','Сцена 20. Собор'],
            ['4848-5332','Сцена 21. Вальпургиева ночь'],
            ['5333-5558','Сцена 22. Сон в Вальпургиеву ночь'],
            ['5559-5590','Сцена 23. Пасмурный день. Поле'],
            ['5591-5604','Сцена 24. Ночь. Открытое поле'],
            ['5605-5883','Сцена 25. Тюрьма'],
        ];



        return new MainPageResponseDTO(
            $textDTOs,
            $metaData, //для всех текстов
            $navLinks,
            $maxTextLength,
            count($textDTOs)
        );
    }
}
