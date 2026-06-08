<?php

namespace Builov\Faust\Application\UseCase;

use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Application\DTO\TextDTO;
use Builov\Faust\Application\Mapper\TextMapper;

class GetSingleTextUseCase
{
    public function __construct(
        private TextRepositoryInterface $repository
    )
    {
    }

    public function execute(string $id): TextDTO
    {
        $allMeta = $this->repository->getAllMeta();
        if (!isset($allMeta[$id])) {
            throw new \InvalidArgumentException("Translation not found: " . $id);
        }

        $poemText = $this->repository->getById($id);
        return TextMapper::toDTO($poemText, $allMeta[$id]);
    }
}
