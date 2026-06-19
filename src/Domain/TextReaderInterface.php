<?php

namespace Builov\Faust\Domain;

use Builov\Faust\Domain\Model\TextMeta;

interface TextReaderInterface
{
    /**
     * @param TextMeta $meta
     * @return string[]
     */
    public function readText(TextMeta $meta): array;
}
