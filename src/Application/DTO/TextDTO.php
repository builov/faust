<?php

namespace Builov\Faust\Application\DTO;

class TextDTO
{

    /** @param TextFragmentDTO[] $fragments */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly array  $fragments
    ) {}
}
