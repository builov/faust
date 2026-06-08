<?php

namespace Builov\Faust\Domain;

class TextConfig
{
    public function __construct(
        public string $path,
        public string $title,
        public array $startsFrom = [],
        public array $markup = []
    ) {}
}
