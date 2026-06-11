<?php
/**
 * Создает экземпляры Twig, Репозиториев и Контроллеров, связывая их вместе.
 */

namespace Builov\Faust\Infrastructure\DI;

use Builov\Faust\Application\UseCase\GetSingleTextUseCase;
use Builov\Faust\Application\UseCase\GetTextsUseCase;
use Builov\Faust\Application\UseCase\TranslateLinesUseCase;
use Builov\Faust\Infrastructure\Controller\TranslateLinesApiController;
use Builov\Faust\Infrastructure\Repository\TextRepository;
use Builov\Faust\Infrastructure\Storage\FileTextReader;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;
use Builov\Faust\Infrastructure\Controller\MainPageController;
use Builov\Faust\Infrastructure\Controller\SingleTextApiController;
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
        $configPath = $baseDir . 'data/config.json'; // Путь к вашему config.json

        // 2. Сборка слоев (снизу вверх)
        $configReader = new JsonConfigReader($configPath);
//        print_r($configReader->read()); exit;

        $textReader = new FileTextReader($baseDir . 'data');

        $repository = new TextRepository($configReader, $textReader);
//        print_r($repository->getById('aksakov')); exit;

        $cacheStorage = new PhpFileCacheStorage($baseDir . 'data/trans.php');

        $getTextsUseCase = new GetTextsUseCase($repository);
        $getSingleTextUseCase = new GetSingleTextUseCase($repository);
        $translateLinesUseCase = new TranslateLinesUseCase($cacheStorage);

        // 3. Возвращаем плоский "контейнер" (карта классов)
        return [
            MainPageController::class => new MainPageController($getTextsUseCase, $twig),
            SingleTextApiController::class => new SingleTextApiController($getSingleTextUseCase),
            TranslateLinesApiController::class => new TranslateLinesApiController($translateLinesUseCase)
        ];
    }
}
