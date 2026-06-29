<?php
namespace Builov\Faust\Application;

//use Builov\Faust\Infrastructure\ConfigRepositoryInterface;
use Builov\Faust\Infrastructure\FileReader;
use Builov\Faust\Domain\TextMarkuper;
use Builov\Faust\Domain\TextConfig;
use Builov\Faust\Infrastructure\LinesRepository;

class FaustReaderService
{
    public function __construct(
        private ConfigRepositoryInterface $configRepository,
        private FileReader $fileReader,
        private TextMarkuper $textMarkuper,
        private LinesRepository $linesRepository
    ) {}

    public function getMeta(): array
    {
        return $this->configRepository->getAll();
    }

    /**
     * Метод для переиндексации всех .txt файлов в trans.php
     */
    public function buildIndex(string $dataDir): void
    {
        $files = glob($dataDir . '*.txt');
        $allArrays = [];
        $meta = $this->getMeta();

        foreach ($files as $filePath) {
            $key = pathinfo($filePath, PATHINFO_FILENAME);

            if (isset($meta[$key])) {
                $allArrays[$key] = $this->getLineArray($key);
            }
        }

        $this->linesRepository->saveIndex($allArrays);
    }

    /**
     * Внутренний метод для получения «чистых» строк для генерации trans.php
     */
    private function getLineArray(string $id): array
    {
        $config = $this->configRepository->findById($id);
        if (!$config) {
            return [];
        }

        $lines = $this->fileReader->readLines($config->path);
        if (empty($lines)) {
            return [];
        }

        $lineIndex = 0;
        $textRaw = [];
        $chunkIndex = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === "" || str_starts_with($line, "<title>")) {
                continue;
            }
            if ($line === "DELIMITER") {
                $chunkIndex++;
                continue;
            }

            if ($line === "empty_line") {
                $lineIndex++;
            } else {
                $textRaw[$chunkIndex][$lineIndex] = $line;
                $lineIndex++;
            }
        }

        if (empty($config->startsFrom)) {
            return $textRaw[0] ?? [];
        }

        $result = [];
        foreach ($config->startsFrom as $fragmentKey => $startLine) {
            if (!isset($textRaw[$fragmentKey]) || empty($textRaw[$fragmentKey])) {
                continue;
            }

            $firstKeyInFragment = array_key_first($textRaw[$fragmentKey]);

            foreach ($textRaw[$fragmentKey] as $originalIndex => $line) {
                $finalIndex = ($startLine - 1) + ($originalIndex - $firstKeyInFragment);
                $result[$finalIndex] = $line;
            }
        }

        return $result;
    }

    /**
     * Чтение текста с разметкой
     */
    public function read(string $identifier): array|false
    {
        $config = $this->configRepository->findById($identifier);
        if (!$config) {
            return false;
        }

        $rawLines = $this->fileReader->readLines($config->path);
        if (empty($rawLines)) {
            return false;
        }

        // Парсинг блоков текста с учетом <title> и DELIMITER
        $textChunks = [];
        $fragmentTitle = '';
        $chunkIndex = 0;

        foreach ($rawLines as $line) {
            $line = trim($line);
            if ($line === "") {
                continue;
            }
            if ($line === "DELIMITER") {
                $textChunks[$chunkIndex][0] .= $fragmentTitle;
                $chunkIndex++;
                continue;
            }
            if (str_starts_with($line, "<title>")) {
                $fragmentTitle = '<div class="floating-title scene_title">' .
                    str_replace(["<title>", "</title>"], '', $line) . '</div>';
                continue;
            }

            $textChunks[$chunkIndex][] = ($line === "empty_line") ? "" : $line;
        }

        // Сборка финального массива строк по логике фрагментов
        $assembledLines = $this->assembleFragments($textChunks, $config);

        // Применение разметки стилей
        $markupLines = [];
        if (isset($config->markup[0])) {
            $markupLines = $this->fileReader->readLines($config->markup[0]);
        }

        return $this->textMarkuper->applyMarkup($assembledLines, $markupLines);
    }

    private function assembleFragments(array $textChunks, TextConfig $config): array
    {
        if (empty($config->startsFrom)) {
            return $textChunks[0] ?? [];
        }

        $result = [];
        $startIndex = 0;

        foreach ($config->startsFrom as $fragmentKey => $startLine) {
            if (!isset($textChunks[$fragmentKey])) {
                continue;
            }
            $emptyLinesCount = $startLine - ($startIndex + 1);
            if ($emptyLinesCount > 0) {
                $result = array_merge($result, array_fill(0, $emptyLinesCount, ''));
            }
            $result = array_merge($result, $textChunks[$fragmentKey]);
            $startIndex = count($result);
        }

        return $result;
    }
}
