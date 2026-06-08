<?php

namespace Builov\Faust\Application\Mapper;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Application\DTO\TextLineDTO;

class TextMapper
{
    public static function toDTO(Text $poemText, TextMeta $meta): TextDTO
    {
        $lineDTOs = [];
        foreach ($poemText->getLines() as $number => $line) {
            $lineDTOs[$number] = new TextLineDTO(
                $line->getNumber(),
                $line->getText(),
                $line->getCssClass()
            );
        }

        return new TextDTO(
            $poemText->getId(),
            $meta->getTitle(),
            $lineDTOs
        );
    }
}
