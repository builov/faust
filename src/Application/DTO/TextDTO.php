<?php

namespace Builov\Faust\Application\DTO;

class TextDTO
{
    /** @param TextLineDTO[] $lines */
    public function __construct(
        public string $id,
        public string $title,
        public array  $lines
    )
    {
    }
}
