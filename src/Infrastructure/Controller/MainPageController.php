<?php
/**
 * Отвечает за сценарий, когда $selected является массивом.
 * Принимает HTTP-запрос.
 * Вызывает Use Case для получения списка выбранных текстов и метаданных.
 * Передает результат в Twig-шаблон.
 */

namespace Builov\Faust\Infrastructure\Controller;

use Builov\Faust\Application\UseCase\GetMainPageUseCase;
use Builov\Faust\Domain\VO\LineRange;
use Builov\Faust\Infrastructure\Http\HtmlResponse;
use Builov\Faust\Infrastructure\Http\Response;
use Twig\Environment;

class MainPageController
{
    public function __construct(
        private GetMainPageUseCase $useCase,
        private Environment        $twig
    ) {}

    /** @param string[] $selectedIds */
    public function handle(array $selectedIds): Response
    {
        $lineRange = LineRange::fromQueryParams($_GET);

//        if (isset($_GET['lines'])) {
//            if (preg_match('/^(\d+)-(\d+)$/', trim($_GET['lines']), $matches)) {
//                $lineFrom = (int)$matches[1];
//                $lineTo = (int)$matches[2];
//
//                try {
//                    $lineRange = new LineRange($lineFrom, $lineTo);
//                } catch (\InvalidArgumentException $e) {
//                    $lineRange = LineRange::all();
//                }
//            }
//        }

        $pageData = $this->useCase->execute($selectedIds, $lineRange);

        //конвертация из TextDTO[] в простой массив для шаблона
        $texts = [];
        foreach ($pageData->texts as $textId => $textDTO) {
            foreach ($textDTO->fragments as $fragment) {
                foreach ($fragment->lines as $line) {
                    if ($line) {
                        $texts[$textId][] = [
                            $line->text,
                            $line->semantics,
                            $line->number
                        ];
                    } else { // пустые строки
                        $texts[$textId][] = [
                            '',
                            '',
                            ''
                        ];
                    }
                }
            }
        }

//        print_r($texts); exit;

        $html = $this->twig->render('index.html.twig', [
            'title' => 'Фауст',
            'data' => $texts,
            'selected' => array_keys($texts),
            'columns' => $pageData->columnsCount,
            'meta' => $pageData->meta,
        ]);

        return new HtmlResponse($html);
    }
}
