<?php

namespace Builov\Faust\Infrastructure\Repository;

use Builov\Faust\Domain\Model\TextBuilderInterface;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;

class TextRepository implements TextRepositoryInterface
{
    private ?array $metaCache = null;

    public function __construct(
        private JsonConfigReader $configReader,
        private TextBuilderInterface $textBuilder
    )
    {
    }

    public function getById(string $id): Text
    {
        $metaList = $this->getAllMeta();
        if (!isset($metaList[$id])) {
            throw new \RuntimeException("Metadata not found for ID: " . $id);
        }

        return $this->textBuilder->buildText($metaList[$id]);
    }

    public function getAllMeta(): array
    {
        if ($this->metaCache === null) {
            $this->metaCache = $this->configReader->read();
        }
        return $this->metaCache;
    }
}
