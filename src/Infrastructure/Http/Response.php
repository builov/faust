<?php
/**
 * Базовый класс ответа
 */

namespace Builov\Faust\Infrastructure\Http;

abstract class Response
{
    public function __construct(
        protected string $content,
        protected int    $statusCode = 200,
        protected array  $headers = []
    )
    {
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;
    }
}
