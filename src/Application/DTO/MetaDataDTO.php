<?php

namespace Builov\Faust\Application\DTO;

class MetaDataDTO
{
    /** @param int[] $startsFrom */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly array  $startsFrom
    ) {}
}
