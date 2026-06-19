<?php

namespace Builov\Faust\Infrastructure\Storage;

use Builov\Faust\Domain\Model\TextMeta;

class JsonConfigReader
{
    public function __construct(
        private string $configPath
    ) {}

    /** @return TextMeta[] */
    public function read(): array
    {
        $json = file_get_contents($this->configPath);
        $data = json_decode($json, true);

        $meta = [];
        foreach ($data as $id => $item) {
            $meta[$id] = new TextMeta(
                $id,
                $item['title'],
                $item['path'],
                $item['markup'] ?? [],
                $item['starts_from'] ?? []
            );
        }
        return $meta;
    }
}
