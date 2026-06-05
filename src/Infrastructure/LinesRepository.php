<?php

namespace Builov\Faust\Infrastructure;

use RuntimeException;

class LinesRepository
{
    private ?array $lines = null;

    public function __construct(private string $dataDir) {}

    /**
     * Ленивая загрузка всех проиндексированных строк из файла trans.php
     */
    public function all(): array
    {
        if ($this->lines === null) {
            $path = $this->dataDir . 'trans.php';
            if (!file_exists($path)) {
                return [];
            }
            $this->lines = require $path;
        }
        return $this->lines;
    }

    /**
     * Возвращает массивы строк, сгруппированных по переводам
     */
    public function getTranslationsForLines(array $lineNumbers): ?array
    {
        $cleanLines = filter_var_array($lineNumbers, FILTER_VALIDATE_INT);
        $cleanLines = array_filter($cleanLines, function($value) {
            return $value !== false && $value !== null;
        });

        if (empty($cleanLines)) {
            return null;
        }

        $output = [];
        $flippedLines = array_flip($cleanLines);

        foreach ($this->all() as $translationID => $translationLines) {
            $foundTexts = array_intersect_key($translationLines, $flippedLines);

            if (!empty($foundTexts)) {
                $output[] = [
                    'label' => (string)$translationID,
                    'texts' => $foundTexts
                ];
            }
        }

        return empty($output) ? null : $output;
    }

    public function reload(): void
    {
        $this->lines = null;
    }

    /**
     * Сохранение проиндексированных переводов в PHP-файл
     */
    public function saveIndex(array $allArrays): void
    {
        $exported = var_export($allArrays, true);
        $phpCode = "<?php\n\n// Автоматически сгенерированный файл данных\n// Создан: " . date('Y-m-d H:i:s')
            . "\n// Всего массивов: " . count($allArrays) . "\n\nreturn " . $exported . ";\n";

        $targetFile = $this->dataDir . 'trans.php';
        if (file_put_contents($targetFile, $phpCode) === false) {
            throw new RuntimeException("Ошибка записи в $targetFile\n");
        }

        $this->reload();
    }
}
