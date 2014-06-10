<?php
require_once("Picture.class.php");

echo Picture::decrypt($argv[1], $argv[2])->getOutput();
echo "\n";
?>
