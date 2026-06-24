<?php

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\TranslateLinesUseCase;
use Builov\Faust\Infrastructure\Http\JsonResponse;
use Builov\Faust\Infrastructure\Http\Response;

class TranslationListApiController
{
    public function __construct(
        private TranslateLinesUseCase $useCase
    ) {}

    /**
     * @param array $lineNumbers
     * @return Response
     */
    public function handle(array $lineNumbers): Response
    {
        // Очищаем входящий массив: оставляем только целые числа
        // array_filter без второго аргумента удалит нули (если ID строки равен 0),
        // поэтому используем строгую валидацию через filter_var_array
        $lines = filter_var_array($lineNumbers, FILTER_VALIDATE_INT);

        // Удаляем из массива элементы, которые не прошли валидацию (равны false или null)
        $lines = array_filter($lines, function($value) {
            return $value !== false && $value !== null;
        });

        if (empty($lines)) {
            return new JsonResponse(['error' => 'Incorrect line numbers'], 400);
        }

        try {
            $result = $this->useCase->getTranslationList($lines);

            return new JsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
}
