<?php

namespace Builov\Faust\Application;

use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Application\DTO\TextFragmentDTO;
use Builov\Faust\Application\DTO\TextLineDTO;
use Builov\Faust\Domain\Model\Text;

class TextMapper
{
    public static function toDTO(Text $text): TextDTO
    {
        $fragmentDTOs = [];

        foreach ($text->getFragments() as $fragment) {
            $lineDTOs = [];

            foreach ($fragment->getLines() as $line) {
                $lineDTOs[] = new TextLineDTO(
                    $line->getNumber(),
                    $line->getText(),
                    $line->getCssClass() // Базовая семантика из домена
                );
            }

            $fragmentDTOs[] = new TextFragmentDTO(
                $fragment->getTitle(),
                $lineDTOs
            );
        }

        return new TextDTO($text->getId(), $text->getTitle(), $fragmentDTOs);
    }
}
