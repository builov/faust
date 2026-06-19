<?php

namespace Builov\Faust\Application\DTO;

class TextLineDTO
{
    public function __construct(
        public readonly int    $number,
        public string $text,
        public string $semantics
    ) {}
}
