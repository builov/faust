<?php

namespace Builov\Faust\Domain\Model;

interface TextReaderInterface
{
    /**
     * @return TextLine[]|string[]
     */
    public function readLines(TextMeta $meta): array;
}
