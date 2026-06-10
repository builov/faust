<?php

namespace Builov\Faust\Infrastructure\Storage;
/**
 * обертка (декоратор). Вместо медленного чтения файлов использует
 * быстрый файл-индекс (бывший trans.php).
 *
 * Реализует генерацию и чтение файла trans.php (двухуровневого массива строк).
 * Отвечает за методы all() и getLineArray().
 */

class PhpFileCacheStorage
{
    public function __construct(private string $cacheFilePath) {}

    public function write(array $data): void
    {
        // Генерируем валидный PHP-код, возвращающий массив
        $code = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($this->cacheFilePath, $code);
    }

    public function read(): array
    {
        if (file_exists($this->cacheFilePath)) {
            return require $this->cacheFilePath;
        }
        return [];
    }
}
