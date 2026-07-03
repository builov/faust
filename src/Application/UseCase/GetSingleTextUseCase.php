<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Application\TextMapper;
use Builov\Faust\Application\TextMarkupService;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\VO\LineRange;

class GetSingleTextUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository,
        private TextMarkupService $markupService
    )
    {
    }

    public function execute(string $id, LineRange $lineRange): TextDTO
    {
        $allMeta = $this->repository->getAllMeta();

        if (!isset($allMeta[$id])) {
            throw new \InvalidArgumentException("Translation not found: " . $id);
        }

//        $text = $this->repository->getById($id);

        if (isset($allMeta[$id])) {
            $text = $this->repository->getById($id);
            // DTO с разметкой
            $textDTO = $this->markupService->applyMarkup(TextMapper::toDTO($text), $allMeta[$id], $lineRange);
            // без разметки
//                $textDTOs[] = TextMapper::toDTO($text);
        }

//        print_r(TextMapper::toDTO($text)); exit;

        return $textDTO;
    }
}
