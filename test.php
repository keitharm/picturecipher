<?php
require_once("Picture.class.php");
$enc = Picture::encrypt($argv[1], $argv[2]);
echo $enc->getResult() . "\n";

$dec = Picture::decrypt($enc->getResult(), $argv[2]);
echo $dec->getResult();

echo "\n";
?>
