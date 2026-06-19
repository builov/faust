<?php

namespace Builov\Faust\Domain\Model;

interface TextReaderInterface
{
    /**
     * @param TextMeta $meta
     * @return string[]
     */
    public function readText(TextMeta $meta): array;
}
