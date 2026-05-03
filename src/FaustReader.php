<?php
namespace Builov\Faust;

// "title",
// "subtitle",
// "scene_title",
// "scene_subtitle",
// "stage_direction", общепринятый технический термин для всех указаний автора в пьесе (движения, свет, декорации)
// "speech_heading", имя персонажа над его репликой
// "extension", техническая пометка рядом с именем персонажа, указывающая, как слышен голос (например, O.S. — Off-Screen / за сценой).
// "parenthetical", короткая ремарка внутри реплики, обычно в скобках, указывающая на эмоцию или действие (например, «(smiling)»).
// "entrance", Entrance / Exit — технические пометки о выходе персонажа на сцену или уходе с неё.
// "exit",
// "stanza_start",
// "stanza_end"
// "aside" короткая ремарка «в сторону», которую персонаж произносит для зрителей, но которую «не слышат» другие герои на сцене.
// Character list (или Dramatis Personae для всей пьесы) — список действующих лиц конкретной сцены.
// Slugline — профессиональный термин для строки заголовка, которая задает место и время действия (например, EXT. GARDEN - DAY).

class FaustReader
{
    private array $files;

    public function __construct()
    {
        $this->files = [
            'faust' => './../data/faust.txt',
            'faust_markup' => './../data/faust_markup.txt',
            'holodkovskiy' => './../data/holodkovskiy.txt',
//            'holodkovskiy_markup' => './../data/holodkovskiy_markup.txt',
            'minaev' => './../data/minaev.txt'
        ];
    }

    public function read(string $identifier): array|false
    {
        if (!isset($this->files[$identifier])) {
            return false;
        }

        $data = file($this->files[$identifier], FILE_IGNORE_NEW_LINES);

        $result = array_values(array_filter($data, fn($value) => trim($value) !== "")); //или array_filter($data); - с сохранением пустых строк

        if ($identifier == 'minaev') {
            $startFrom = 35;

//            print_r($result); exit;

            $emptyLinesArr = array_fill(0, $startFrom, '');

            $result = array_merge($emptyLinesArr, $result);

//            print_r($result); exit;
        }

        return $result;
    }

//    public function addFile(string $identifier, string $path): void
//    {
//        $this->files[$identifier] = $path;
//    }
}
