<?php

namespace Builov\Faust\Domain\Model;

interface TextReaderInterface
{
    /**
     * @param TextMeta $meta
     * @return Text
     */
    public function readText(TextMeta $meta): Text;
}
