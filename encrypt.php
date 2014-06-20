<?php
require_once("Picture.class.php");

$pic = Picture::encrypt(file_get_contents($argv[1]), $argv[2]);
$pic->outputImage();
?>
