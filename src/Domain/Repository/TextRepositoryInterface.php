<?php
/**
 * контракт для поиска и сохранения текстов.
 * Абстракция для поиска и сохранения текстов, метаданных и связей между ними.
 */

namespace Builov\Faust\Domain\Repository;

use Builov\Faust\Domain\Model\Text;
use Builov\Faust\Domain\Model\TextMeta;

interface TextRepositoryInterface
{
    public function getById(string $id): Text;

    /** @return TextMeta[] */
    public function getAllMeta(): array;

    public function getMetaById(string $id): TextMeta;
}
