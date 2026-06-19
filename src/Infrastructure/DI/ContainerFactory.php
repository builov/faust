<?php
/**
 * Создает экземпляры Twig, Репозиториев и Контроллеров, связывая их вместе.
 */

namespace Builov\Faust\Infrastructure\DI;

use Builov\Faust\Application\TextBuilder;
use Builov\Faust\Application\TextMarkupService;
use Builov\Faust\Application\UseCase\GetMainPageUseCase;
use Builov\Faust\Application\UseCase\GetSingleTextUseCase;
use Builov\Faust\Application\UseCase\RebuildCacheUseCase;
use Builov\Faust\Application\UseCase\TranslateLinesUseCase;
use Builov\Faust\Infrastructure\Controller\BuildCacheController;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;
use Builov\Faust\Infrastructure\Repository\TextRepository;
use Builov\Faust\Infrastructure\Storage\FileTextReader;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;
use Builov\Faust\Infrastructure\Storage\PhpFileCacheStorage;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ContainerFactory
{
    public static function build(): array
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $twig = new Environment($loader, ['cache' => false, 'debug' => true]);

        $baseDir = __DIR__ . '/../../../';
        $configPath = $baseDir . 'data/config.json';
        $dataDir = $baseDir . 'data';

        $configReader = new JsonConfigReader($configPath);
        $cacheStorage = new PhpFileCacheStorage($baseDir . 'data/trans.php');

        $textReader = new FileTextReader($dataDir);
        $textBuilder = new textBuilder($textReader);



        $repository = new TextRepository($configReader, $textBuilder);

        $markupService = new TextMarkupService($dataDir);

        // 4. Разделяем репозитории в зависимости от полноты обработки данных
//        $fullRepository = new TextRepository($configReader, $fullReader);
//        $rawRepository  = new TextRepository($configReader, $readerWithStructure);
//        print_r($fullRepository->getById('aksakov')); exit;

        $getMainPageUseCase = new GetMainPageUseCase($repository, $markupService);
        $getSingleTextUseCase = new GetSingleTextUseCase($repository, $markupService);
        $translateLinesUseCase = new TranslateLinesUseCase($cacheStorage);
        $rebuildCacheUseCase = new RebuildCacheUseCase($repository, $cacheStorage);

        return [
            MainPageController::class => new MainPageController($getMainPageUseCase, $twig),
            SingleTextApiController::class => new SingleTextApiController($getSingleTextUseCase),
            TranslateLinesApiController::class => new TranslateLinesApiController($translateLinesUseCase),
            BuildCacheController::class => new BuildCacheController($rebuildCacheUseCase)
        ];
    }
}
