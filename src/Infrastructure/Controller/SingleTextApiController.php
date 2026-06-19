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

class SingleTextApiController
{
    public function __construct(
        private GetSingleTextUseCase $useCase
    )
    {
    }

    public function handle(string $id): Response
    {
        $cleanId = htmlspecialchars(trim($id), ENT_QUOTES, 'UTF-8');

        try {
            $textDTO = $this->useCase->execute($cleanId);

            //конвертация из $textDTO в массив попроще (-1 уровень)
//            $result = array_map(function ($line) {
//                return [
//                    $line->text,
//                    $line->cssClass
//                ];
//            }, $textDTO->lines);



            foreach ($textDTO->fragments as $fragment) {
                foreach ($fragment->lines as $line) {
                    if ($line) {
                        $result[] = [
                            $line->text,
                            $line->semantics
                        ];
                    } else { // пустые строки
                        $result[] = [
                            '',
                            ''
                        ];
                    }
                }
            }

//            print_r($result); exit;

            return new JsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
}
