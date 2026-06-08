<?php

namespace Builov\Faust\Infrastructure;

use Builov\Faust\Application\ConfigRepositoryInterface;
use Builov\Faust\Domain\TextConfig;
use RuntimeException;

class JsonConfigRepository implements ConfigRepositoryInterface
{
    private array $configs = [];

    public function __construct(string $dataDir)
    {
        $path = $dataDir . 'config.json';
        if (!file_exists($path)) {
            throw new RuntimeException("Конфигурационный файл не найден: {$path}");
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

//        print_r($data); exit;

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Ошибка чтения JSON: " . json_last_error_msg());
        }

        foreach ($data as $id => $config) {
            $this->configs[$id] = new TextConfig(
                path: $config['path'],
                title: $config['title'],
                startsFrom: $config['starts_from'] ?? [],
                markup: $config['markup'] ?? []
            );
        }
    }

    public function getAll(): array
    {
//        print_r($this->configs); exit;

        return $this->configs;
    }

    public function findById(string $id): ?TextConfig
    {
        return $this->configs[$id] ?? null;
    }
}
