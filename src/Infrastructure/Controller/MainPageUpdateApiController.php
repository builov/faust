<?php

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\GetTextsByRangeUseCase;
use Builov\Faust\Domain\VO\LineRange;
use Builov\Faust\Infrastructure\Http\JsonResponse;
use Builov\Faust\Infrastructure\Http\Response;

class MainPageUpdateApiController
{
    public function __construct(
        private GetTextsByRangeUseCase $useCase
    ) {}

    public function handle($lines, array $selectedIds): Response
    {
        $lineRange = LineRange::fromQueryParams($_GET);

        $textDTOs = $this->useCase->execute($selectedIds, $lineRange);

        print_r($textDTOs); exit;

        try {
            $textDTO = $this->useCase->execute($selectedIds, $lineRange);

            //конвертация из TextDTO в простой массив для шаблона
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