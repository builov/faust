<?php

namespace Builov\Faust\Domain\Repository;

interface CacheStorageInterface
{
    public function write(array $data): void;

    public function read(): array;
}