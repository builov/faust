<?php

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\RebuildCacheUseCase;
use Builov\Faust\Infrastructure\Http\HtmlResponse;
use Builov\Faust\Infrastructure\Http\Response;

class BuildCacheController
{
    public function __construct(
        private RebuildCacheUseCase $useCase
    )
    {
    }

    public function handle(): Response
    {
        $result = $this->useCase->execute();

//        print_r($pageData->meta); exit;

        $html = $result['success-message'];

        return new HtmlResponse($html);
    }
}