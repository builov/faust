<?php

namespace Builov\Faust\Application\DTO;

class MainPageResponseDTO
{
    /**
     * @param TextDTO[] $texts
     * @param array $meta Массив сырых данных мета для кнопок
     */
    public function __construct(
        public array $texts,
        public array $meta,
        public array $navLinks,
        public int   $maxTextLength,
        public int   $columnsCount
    ) {}
}
