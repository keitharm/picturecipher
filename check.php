<?php
ini_set('memory_limit','-1');
require_once("Picture.class.php");

$pic = Picture::check($argv[1]);
print_r($pic->getMeta());
?>
