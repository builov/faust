<?php
use Builov\Faust\FaustReader;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

$reader = new FaustReader();
echo json_encode($reader->getMeta());