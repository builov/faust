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

        try {

            $texts = [];
            foreach ($textDTOs as $textId => $textDTO) {
                foreach ($textDTO->fragments as $fragment) {
                    foreach ($fragment->lines as $line) {
                        if ($line) {
                            $texts[$textId][] = [
                                $line->text,
                                $line->semantics,
                                $line->number
                            ];
                        } else { // пустые строки
                            $texts[$textId][] = [
                                '',
                                '',
                                ''
                            ];
                        }
                    }
                }
            }

            return new JsonResponse($texts);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
}