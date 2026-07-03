<?php

namespace Builov\Faust\Infrastructure\Repository;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextMeta;
use Builov\Faust\Domain\Repository\TextRepositoryInterface;
use Builov\Faust\Domain\TextBuilderInterface;
use Builov\Faust\Domain\VO\LineRange;
use Builov\Faust\Infrastructure\Storage\JsonConfigReader;

class TextRepository implements TextRepositoryInterface
{
    private ?array $metaCache = null;

    public function __construct(
        private JsonConfigReader $configReader,
        private TextBuilderInterface $textBuilder
    ) {}

    public function getById(string $id): Text
    {
        $metaList = $this->getAllMeta();
        if (!isset($metaList[$id])) {
            throw new \RuntimeException("Metadata not found for ID: " . $id);
        }

//        print_r($this->textBuilder->buildText($metaList[$id])); exit;

        return $this->textBuilder->buildText($metaList[$id]);
    }

    public function getRangeById(string $id, LineRange $lineRange): Text
    {
        $text = $this->getById($id);

//        return $this->textBuilder->buildTextRange($metaList[$id], $lineRange);

        return $text;
    }

    public function getAllMeta(): array
    {
        if ($this->metaCache === null) {
            $this->metaCache = $this->configReader->read();
        }
        return $this->metaCache;
    }

    public function getMetaById(string $id): TextMeta
    {
        if ($this->metaCache === null) {
            $this->metaCache = $this->configReader->read();
        }

        return $this->metaCache[$id];
    }
}
