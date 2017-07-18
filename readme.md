# PHP Class pixelate

## What it is

Pixelate is a PHP based class, which loads jpeg images and manipulate them.
Load any jpg/jpeg file via constructor. rasterizes the image pixel by pixel into custom chunks.
Then get the average color of every chunk an recreate the image out of the chunks.

There are no file security checks, yet. Please use in any way, but not for production environments

## Requirements

* PHP 7
* GD Lib

## Get started

	require_once('class.pixelate.php');

	// class.pixelate([string] SOURCE_PATH, [string] NOTUSED_OUTPUT, [int] CHUNKSIZE_WIDTH_IN_PIXEL, [int] CHUNKSIZE_HEIGHT_IN_PIXEL);
	$pixelate = new pixelate('picture.jpg','unused.jpg', 40, 40);

	//sets image information
	$pixelate->setResolution();

	//show nice chunksizes
	$pixelate->showPossibleChunkSize();
	
	//create chunks from image an save average color
	$pixelate->createChunks();

	//show some information
	$pixelate->debug();
	
	//show original image
	$pixelate->rawImage();

	//render !HTML Based! version of the average chunks (many, many, many <spans>)	
	$pixelate->renderImage();

	//Recreate as new image
	$pixelate->createImage();
