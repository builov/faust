<?php

namespace Builov\Faust;

class LinesRepository
{
    /** @var array|null Лениво загруженный массив */
    private static ?array $lines = null;

    /**
     * Вернуть все данные.
     * При первом вызове подгружает файл с данными и кеширует в статике.
     */
    public static function all(): array
    {
        if (self::$lines === null) {
            self::$lines = require DATA_DIR . 'trans.php';
        }
        return self::$lines;
    }

//    public static function get(string $arrayId): array
//    {
//        return self::all()[$arrayId] ?? [];
//    }
//    public static function item(string $arrayId, int $index): ?string
//    {
//        return self::all()[$arrayId][$index] ?? null;
//    }

    /**
     * массив переводов (строк id) для определенной строки
     * @param int $index
     * @return array|null
     */
//    public static function getTranslationsForLine(int $index): ?array
//    {
//        $output = [];
//        foreach (self::all() as $translationID => $translation) {
//            if (isset($translation[$index])) {
//                $output[] = [
//                    'label' => $translationID,
//                    'texts'  => [$translation[$index]]
//                ];
//            }
//        }
//
//        return $output;
//    }

    /**
     * Возвращает массивы строк, сгруппированных по переводам, для заданного массива номеров строк
     * @param array $lineNumbers Массив искомых номеров строк, например [5, 12, 43]
     * @return array|null
     */
    public static function getTranslationsForLines(array $lineNumbers): ?array
    {
        // Очищаем входящий массив: оставляем только целые числа
        // array_filter без второго аргумента удалит нули (если ID строки равен 0),
        // поэтому используем строгую валидацию через filter_var_array
        $cleanLines = filter_var_array($lineNumbers, FILTER_VALIDATE_INT);

//        var_dump($cleanLines); exit;

        // Удаляем из массива элементы, которые не прошли валидацию (равны false или null)
        $cleanLines = array_filter($cleanLines, function($value) {
            return $value !== false && $value !== null;
        });

        if (empty($cleanLines)) {
            return null;
        }

        $output = [];

        // Переворачиваем массив номеров строк для быстрого поиска по ключам
        $flippedLines = array_flip($cleanLines);

//        var_dump($flippedLines); exit;

        foreach (self::all() as $translationID => $translationLines) {
            // Мгновенно находим пересечения по ключам на уровне ядра PHP
            $foundTexts = array_intersect_key($translationLines, $flippedLines);

            if (!empty($foundTexts)) {
                $output[] = [
                    'label' => (string)$translationID,
                    'texts' => $foundTexts
                ];
            }
        }

//        var_dump($output); exit;

        return empty($output) ? null : $output;
    }


    /**
     * Принудительно перезагрузить данные (например, если файл обновился в том же процессе).
     */
    public static function reload(): void
    {
        self::$lines = null;
    }

    /**
     * генератор файла - массива строк переводов для поиска всех переводов по номеру строки (trans.php)
     * @return void
     *
     * Важно для файла trans.php:
     * - При включённом opcache.enable=1 PHP-файл парсится один раз и сохраняется в разделяемой памяти как байт-код. Все последующие запросы не читают и не парсят файл заново.
     * - require с возвратом массива — это штатная операция, которая просто отдаёт готовый zval-массив из памяти OPcache.
     * - 180 000 строк — около 5–15 МБ в памяти, для OPcache некритично.
     * Ограничение: данные нельзя менять без очистки OPcache (перезагрузка PHP-FPM или вызов opcache_invalidate()).
     * Сброс OPcache:
     * - выполнить из веб-контекста или из CLI: opcache_reset(); Очищает весь кеш (и opcode, и файловый кеш). Минус — если у вас несколько PHP-FPM пулов или процессов, эта функция сбрасывает OPcache только в том процессе, который её вызвал. В многопроцессном окружении (FPM) придётся вызывать её в каждом воркере (например, через запрос на специальный эндпоинт, который обойдёт все процессы, либо перезапустить FPM).
     * - перезапуск PHP-FPM (самый надёжный способ, гарантирует полную очистку для всех процессов): systemctl restart php-fpm (или php8.2-fpm и т.п.)
     * - Если массив лежит в отдельном data.php, можно сбросить кеш только для него (такой сброс не затрагивает другие скрипты): opcache_invalidate(__DIR__ . '/data.php', true); Параметр true заставляет перекомпилировать файл при следующем запросе без проверки времени модификации.
     * - Если используется режим opcache.validate_timestamps=0 (продакшн-настройка, когда OPcache не проверяет время изменения файлов), то инвалидация нужна обязательно при любом обновлении файлов — как раз через opcache_invalidate() или перезапуск.
     */
    public function index(): void
    {
        $files = glob(DATA_DIR . '*.txt');

        $allArrays = [];
        foreach ($files as $filePath) {
            // Имя файла без расширения = ключ массива
            $key = pathinfo($filePath, PATHINFO_FILENAME);

            if (isset($this->files[$key])) {
                $allArrays[$key] = $this->getLineArray($key);
            }
        }

        $exported = var_export($allArrays, true); // Используем var_export для создания валидного литерала массива

        $phpCode = "<?php\n\n// Автоматически сгенерированный файл данных\n// Создан: " . date('Y-m-d H:i:s')
            . "\n// Всего массивов: " . count($allArrays) . "\n\nreturn " . $exported . ";\n";

        $targetFile = DATA_DIR . 'trans.php';
        if (file_put_contents($targetFile, $phpCode) === false) {
            die("Ошибка записи в $targetFile\n");
        }
    }
}
