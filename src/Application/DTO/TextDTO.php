<?php

namespace Builov\Faust\Application\DTO;

class TextDTO
{

    /** @param TextFragmentDTO[] $fragments */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public array  $fragments
    ) {}

    public function __clone()
    {
        foreach ($this->fragments as $key => $fragment) {
            $this->fragments[$key] = clone $fragment;
        }
    }
}
