<?php

namespace Builov\Faust\Domain\Model;

interface TextBuilderInterface
{
    /**
     * @param TextMeta $meta
     * @return Text
     */
    public function buildText(TextMeta $meta): Text;
}