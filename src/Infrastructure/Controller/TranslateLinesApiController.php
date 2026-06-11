<?php

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\TranslateLinesUseCase;
use Builov\Faust\Infrastructure\Http\JsonResponse;
use Builov\Faust\Infrastructure\Http\Response;

class TranslateLinesApiController
{
    public function __construct(
        private TranslateLinesUseCase $useCase
    )
    {
    }

    /**
     * @param string $id (номер строки)
     * @return Response
     */
    public function handle(array $lineNumbers, string $trMode): Response
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

        $mode = htmlspecialchars(trim($trMode), ENT_QUOTES, 'UTF-8');

        try {
            $result = $this->useCase->execute($lines, $mode);

            return new JsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
}