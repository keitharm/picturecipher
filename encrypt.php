<?php
require_once("Picture.class.php");

Picture::encrypt($argv[1], $argv[2])->outputImage();
?>
