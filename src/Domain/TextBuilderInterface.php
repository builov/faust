<?php

namespace Builov\Faust\Domain;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextMeta;

interface TextBuilderInterface
{
    /**
     * @param TextMeta $meta
     * @return Text
     */
    public function buildText(TextMeta $meta): Text;
}