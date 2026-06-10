<?php
/**
 * Отвечает за сценарий, когда в $_GET['show'] пришла строка.
 * Вызывает Use Case для получения конкретного перевода.
 * Формирует и отдает JSON-ответ с правильными заголовками.
 */

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\GetSingleTextUseCase;
use Builov\Faust\Infrastructure\Http\JsonResponse;
use Builov\Faust\Infrastructure\Http\Response;

class TranslationApiController
{
    public function __construct(
        private GetSingleTextUseCase $useCase
    )
    {
    }

    public function handle(string $id): Response
    {
        // Очистка входных параметров (санитаризация)
        $cleanId = htmlspecialchars(trim($id), ENT_QUOTES, 'UTF-8');

        try {
            $textDTO = $this->useCase->execute($cleanId);

            //конвертация из $textDTO в массив попроще (-1 уровень)
            $result = array_map(function ($line) {
                return [
                    $line->text,
                    $line->cssClass
                ];
            }, $textDTO->lines);

            return new JsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
}
