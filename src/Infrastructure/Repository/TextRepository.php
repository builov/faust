<?php

namespace Builov\Faust\Infrastructure\Repository;

/**
 * Общий класс-фасад инфраструктуры, который комбинирует JsonConfigReader,
 * FileTextReader и PhpFileCacheStorage,
 * предоставляя приложению чистый интерфейс TranslationRepositoryInterface.
 */

use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;
use Builov\Faust\Infrastructure\Storage\FileTextReader;

class TextRepository implements TextRepositoryInterface
{
    private ?array $metaCache = null;

    public function __construct(
        private JsonConfigReader $configReader,
        private FileTextReader   $textReader
    )
    {
    }

    public function getById(string $id): Text
    {
        $metaList = $this->getAllMeta();
        if (!isset($metaList[$id])) {
            throw new \RuntimeException("Metadata not found for ID: " . $id);
        }

        $meta = $metaList[$id];
        $lines = $this->textReader->readLines($meta);

        return new Text($id, $lines);
    }

    public function getAllMeta(): array
    {
        if ($this->metaCache === null) {
            $this->metaCache = $this->configReader->read();
        }
        return $this->metaCache;
    }
}
