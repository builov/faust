<?php
/**
 * Отвечает за сценарий, когда $selected является массивом.
 * Принимает HTTP-запрос.
 * Вызывает Use Case для получения списка выбранных текстов и метаданных.
 * Передает результат в Twig-шаблон.
 */

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\GetTextsUseCase;
use Builov\Faust\Infrastructure\Http\HtmlResponse;
use Builov\Faust\Infrastructure\Http\Response;
use Twig\Environment;

class MainPageController
{
    public function __construct(
        private GetTextsUseCase $useCase,
        private Environment     $twig
    )
    {
    }

    /** @param string[] $selectedIds */
    public function handle(array $selectedIds): Response
    {
        // Выполняем бизнес-логику и получаем безопасный DTO
        $pageData = $this->useCase->execute($selectedIds);

        //конвертация из массива TextDTO в массив попроще (-1 уровень)
        $result = [];
        foreach ($pageData->texts as $key => $textDTO) {
            $result[$key] = array_map(function ($line) {
                return [
                    $line->text,
                    $line->cssClass
                ];
            }, $textDTO->lines);
        }

        $pageData->texts = $result;
        unset($result);

//        print_r($pageData->meta); exit;

        $html = $this->twig->render('index.html.twig', [
            'title' => 'Фауст',
            'data' => $pageData->texts,
            'selected' => array_keys($pageData->texts),
            'columns' => $pageData->columnsCount,
            'meta' => $pageData->meta,
        ]);

        return new HtmlResponse($html);
    }
}
