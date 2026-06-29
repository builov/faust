<?php

$str = '5544,5568,5605,5618,5627,5634,5671,5684,5698,5713,5719,5871';



$arr = explode(",", $str);
$result = array_map(fn($num) => $num + 10, $arr);
echo implode(',', $result);
