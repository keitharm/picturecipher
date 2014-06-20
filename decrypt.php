<?php
require_once("Picture.class.php");

$pic = Picture::decrypt($argv[1], $argv[2]);
if ($pic->getStatus() == null) {
	echo $pic->getOutput();
} else {
	echo $pic->getStatus() . "\n";
}
?>
