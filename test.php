<?php
require_once("Text.class.php");
$enc = Text::encrypt($argv[1], $argv[2]);
echo $enc->getResult() . "\n";

$dec = Text::decrypt($enc->getResult(), $argv[2]);
echo $dec->getResult();

echo "\n";
?>
