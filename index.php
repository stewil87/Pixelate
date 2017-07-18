<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('class.pixelate.php');

$pixelate = new Pixelate('source.jpg','destination.jpg', 32, 32);
$pixelate->setResolution();

$pixelate->showPossibleChunkSize();
$pixelate->createChunks();

//$pixelate->debug();
$pixelate->rawImage();
$pixelate->renderImage();
$pixelate->createImage();

?>