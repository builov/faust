<?php
/**
 * Создает экземпляры Twig, Репозиториев и Контроллеров, связывая их вместе.
 */

namespace Builov\Faust\Infrastructure\DI;

use Builov\Faust\Application\TextDecorator\HtmlTextDecorator;
use Builov\Faust\Application\UseCase\GetSingleTextUseCase;
use Builov\Faust\Application\UseCase\GetTextsUseCase;
use Builov\Faust\Application\UseCase\RebuildCacheUseCase;
use Builov\Faust\Application\UseCase\TranslateLinesUseCase;
use Builov\Faust\Infrastructure\Controller\BuildCacheController;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;
use Builov\Faust\Infrastructure\Repository\TextRepository;
use Builov\Faust\Infrastructure\Storage\FileTextReader;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
use Builov\Faust\Infrastructure\Storage\MarkupTextDecorator;
use Builov\Faust\Infrastructure\Storage\PhpFileCacheStorage;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ContainerFactory
{
    public static function build(): array
    {
        // 1. Инициализация внешних библиотек и путей
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $twig = new Environment($loader, ['cache' => false, 'debug' => true]);

        $baseDir = __DIR__ . '/../../../';
        $configPath = $baseDir . 'data/config.json';
        $dataDir = $baseDir . 'data';

        // 2. Сборка слоев (снизу вверх)
        $configReader = new JsonConfigReader($configPath);
        $cacheStorage = new PhpFileCacheStorage($baseDir . 'data/trans.php');

//        $textReader = new FileTextReader($baseDir . 'data');

        // 1. Базовый ридер (только чтение из файла)
        $baseReader = new FileTextReader($dataDir);

        // 2. Ридер со структурой (чтение + парсинг <title>, DELIMITER, фрагменты)
        $readerWithStructure = new HtmlTextDecorator($baseReader);

        // 3. Полный ридер (чтение + парсинг + CSS)
        $fullReader = new MarkupTextDecorator($readerWithStructure, $dataDir);

//        $repository = new TextRepository($configReader, $textReader);

        // 4. Разделяем репозитории в зависимости от полноты обработки данных
        $fullRepository = new TextRepository($configReader, $fullReader);
        $rawRepository  = new TextRepository($configReader, $readerWithStructure);
//        print_r($fullRepository->getById('aksakov')); exit;

        $getTextsUseCase = new GetTextsUseCase($fullRepository);
        $getSingleTextUseCase = new GetSingleTextUseCase($fullRepository);
        $translateLinesUseCase = new TranslateLinesUseCase($cacheStorage);
        $rebuildCacheUseCase = new RebuildCacheUseCase($rawRepository, $cacheStorage);

        // 3. Возвращаем плоский "контейнер" (карта классов)
        return [
            MainPageController::class => new MainPageController($getTextsUseCase, $twig),
            SingleTextApiController::class => new SingleTextApiController($getSingleTextUseCase),
            TranslateLinesApiController::class => new TranslateLinesApiController($translateLinesUseCase),
            BuildCacheController::class => new BuildCacheController($rebuildCacheUseCase)
        ];
    }
}
