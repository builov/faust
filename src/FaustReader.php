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
    private array $files = [];

    public function __construct() {
        if ($json = file_get_contents(DATA_DIR . 'config.json')) {

            $this->files = json_decode($json, true);

            if ($this->files === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "Ошибка чтения config.json: " . json_last_error_msg(); exit;
            }
        }
    }

    public function getMeta() {
        return $this->files;
    }

    public function read(string $identifier): array|false
    {
        if (!isset($this->files[$identifier]['path'])) {
            return false;
        }

        /** получение сырого текста */
        $handle = fopen(DATA_DIR . $this->files[$identifier]['path'], "r");
        $textRaw = [];
        $i = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === "") { //удаление пустых строк
                    continue;
                }
                if ($line === "DELIMITER") {
                    $i++;
                    continue;
                }
                if ($line === "empty_line") { //замена empty_line на пустую строку
                    $textRaw[$i][] = "";
                } else {
                    $textRaw[$i][] = $line;
                }
            }
            fclose($handle);
        }

        $startIndex = 0;
        $result = $textRaw[0];

        /** добавление пустых строк (для фрагментов) */
        if (isset($this->files[$identifier]['starts_from'])) {
            $result = [];

//            if (count($textRaw) == 1) {
//                $emptyLinesArr = array_fill($startIndex, $this->files[$identifier]['starts_from'][0] - 1, '');
//                $result = array_merge($emptyLinesArr, $textRaw[0]);
//            } else {
                foreach ($this->files[$identifier]['starts_from'] as $fragmentKey => $startLine) { // обход массива 'starts_from'
                    $emptyLinesArr = array_fill($startIndex, $startLine - ($startIndex + 1), '');
                    $result = array_merge($result, $emptyLinesArr, $textRaw[$fragmentKey]);

                    $startIndex = count($result);
                }

//                print_r($result); exit;
//            }
        }

        /** получение разметки */
        $handle = fopen(DATA_DIR . $this->files[$identifier]['markup'][0], "r");
        $markupArr = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === "") { //удаление пустых строк
                    continue;
                }

                $markupArr[] = $line;
            }
            fclose($handle);
        }

        $markup = [];
        foreach ($markupArr as $line) {
            $type = explode(' / ', $line);

            if (isset($type[1])) {
                foreach (explode(',', $type[1]) as $lineNumber) {
                    $markup[$lineNumber] = $type[0];
                }
            }
        }

        /** разметка сырого текста */
        $textMarkedUp = array_map(function($key, $value) use ($markup) {
            if (isset($markup[$key+1])) {
                return [$value, $markup[$key+1]];
            } else {
                return [$value, 'default'];
            }
        }, array_keys($result), $result);

        return $textMarkedUp;
    }


//    public function getButtons(): array
//    {
//        $files = [];
//        foreach ($this->files as $id => $values) {
//            $files[$id] = $values['title'];
//        }
//
//        return $files;
//    }
}
