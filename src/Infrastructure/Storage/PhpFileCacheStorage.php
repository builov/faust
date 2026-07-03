<?php

namespace Builov\Faust\Infrastructure\Storage;
use Builov\Faust\Domain\Repository\CacheStorageInterface;

/**
 * обертка (декоратор). Вместо медленного чтения файлов использует
 * быстрый файл-индекс (бывший trans.php).
 *
 * Реализует генерацию и чтение файла trans.php (двухуровневого массива строк).
 */

class PhpFileCacheStorage implements CacheStorageInterface
{
    public function __construct(
        private readonly string $cacheFilePath
    ) {}

    public function write(array $data): void
    {
        $exported = var_export($data, true); // Используем var_export для создания валидного литерала массива

        $code = "<?php\n\n// Автоматически сгенерированный файл данных\n// Создан: " . date('Y-m-d H:i:s')
            . "\n// Всего массивов: " . count($data) . "\n\nreturn " . $exported . ";\n";

        if (file_put_contents($this->cacheFilePath, $code) === false) {
            die("Ошибка записи в $this->cacheFilePath\n");
        }
    }

    public function read(): array
    {
        if (file_exists($this->cacheFilePath)) {
            return require $this->cacheFilePath;
        }
        return [];
    }

    public function getTextLinesQuantity($textId): int
    {
        $cache = $this->read();

        //последний индекс соотв. номеру строки и длине массива (1-based indexing)
        return array_key_last($cache[$textId]);
    }
}
