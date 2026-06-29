<?php

namespace Builov\Faust\Infrastructure\Http;

class JsonResponse extends Response
{
    public function __construct(mixed $data, int $statusCode = 200)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        parent::__construct($json, $statusCode, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }
}
