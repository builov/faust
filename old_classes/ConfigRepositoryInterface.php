<?php

namespace Builov\Faust\Application;

use Builov\Faust\Domain\TextConfig;

interface ConfigRepositoryInterface {
    public function getAll(): array;
    public function findById(string $id): ?TextConfig;
}