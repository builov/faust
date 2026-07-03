<?php
declare(strict_types=1);

namespace Builov\Faust\Domain\VO;

use InvalidArgumentException;

/**
 * Неизменяемый объект-значение (Value Object) диапазона строк.
 */
final class LineRange
{
    /**
     * Конструктор автоматически валидирует инварианты при создании.
     * Использование readonly на уровне класса гарантирует, что свойства нельзя изменить после инициализации.
     */
    public function __construct(
        private readonly int $start,
        private readonly int $end
    )
    {
        if ($this->start < 0) {
            throw new InvalidArgumentException("Индекс начала диапазона не может быть отрицательным.");
        }

        if ($this->end < $this->start) {
            throw new InvalidArgumentException("Индекс конца диапазона не может быть меньше индекса начала.");
        }
    }

    /**
     * Фабричный метод для создания VO из сырого массива параметров (например, из HTTP Query)
     */
    public static function fromQueryParams(array $params): self
    {
        $rawLines = $params['lines'] ?? '';

        if (is_string($rawLines) && preg_match('/^(\d+)-(\d+)$/', trim($rawLines), $matches)) {
            try {
                return new self((int)$matches[1], (int)$matches[2]);
            } catch (InvalidArgumentException $e) {
                // Проваливаемся в дефолт, если валидация внутри __construct не прошла
            }
        }

        return self::all();
    }

    /**
     * Создает диапазон, обозначающий "весь текст с самого начала"
     */
    public static function all(): self
    {
        return new self(0, PHP_INT_MAX);
    }

    /**
     * Проверяет, является ли диапазон запросом на весь текст
     */
    public function isAll(): bool
    {
        return $this->end === PHP_INT_MAX;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * Именованный конструктор (Фабричный метод) для пагинации.
     */
    public static function fromPage(int $pageNumber, int $pageSize): self
    {
        if ($pageNumber < 1) {
            throw new InvalidArgumentException("Номер страницы должен начинаться с 1.");
        }

        if ($pageSize < 1) {
            throw new InvalidArgumentException("Размер страницы должен быть больше нуля.");
        }

        $start = ($pageNumber - 1) * $pageSize;
        $end = $start + $pageSize - 1;

        return new self($start, $end);
    }

    /**
     * Возвращает общее количество строк в диапазоне.
     */
    public function getSize(): int
    {
        return $this->end - $this->start + 1;
    }

    /**
     * Проверка равенства двух VO по значению, а не по ссылке.
     */
    public function equals(self $other): bool
    {
        return $this->start === $other->start && $this->end === $other->end;
    }

    /**
     * Проверяет, входит ли конкретный индекс строки в текущий диапазон.
     */
    public function contains(int $lineIndex): bool
    {
        return $lineIndex >= $this->start && $lineIndex <= $this->end;
    }

    /**
     * Безопасный сдвиг диапазона (возвращает новый независимый объект).
     */
    public function shift(int $delta): self
    {
        return new self($this->start + $delta, $this->end + $delta);
    }
}
