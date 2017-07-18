<?php 

/**
 * Pixelate Class written for PHP
 *
 * Pixelate is a PHP based class, which loads jpeg images and manipulate them.
 * Load any jpg/jpeg file via constructor. rasterizes the image pixel by pixel into custom chunks.
 * Then get the average color of every chunk an recreate the image out of the chunks.
 *
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Stefan Wilhelm
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 */

class Pixelate {
    /**
     * relative path to custom image file inc. filename and extension
     * @string
    */
	protected $imagePath;
    /**
     * Readed Image filestream
     * @resource
    */
	protected $imageResource;
    /**
     * X direction Resolution of loaded image
     * @integer
    */
	protected $resolutionWidth;
    /**
     * Y direction Resolution of loaded image
     * @integer
    */
	protected $resolutionHeight;
    /**
     * number of pixel width for chunk
     * @integer
    */
	protected $chunksizeWidth;
    /**
     * number of pixel height for chunk
     * @integer
    */
	protected $chunksizeHeight;
    /**
     * average color collection of every chunk
     * @array
    */
	protected $colorsCollection = array();
    /**
     * Width of Image resolution width / chunksize width
     * @float
    */
	protected $tempImageWidth;
    /**
     * Width of Image resolution height / chunksize height
     * @float
    */
	protected $tempImageHeight;
    /**
     * Width of Image in pixel
     * @int
    */
	protected $resultImageWidth;
    /**
     * number of chunks per row
     * @integer
    */
	protected $resultImageCols;
    /**
     * number of chunk rows
     * @integer
    */
	protected $resultImageRows;
    /**
     * future result image path
     * @string
    */
	protected $resultimagePath;

    /**
     * public function new class([string],[string],[int],[int])
    */
	public function __construct($path, $output, $chunkwidth, $chunkheight){
		$this->imageResource 	= @ImageCreateFromJPEG($path);
		$this->imagePath 		= $path;
		$this->chunksizeWidth 	= $chunkwidth;
		$this->chunksizeHeight 	= $chunkheight;
		$this->resultimagePath 	= $output;
	}

    /**
     * public function debug()
     * @echo
    */
	public function debug(){
		echo "Imagepath: "		.$this->getImagePath()			."<br>";
		echo "Resolution X: "	.$this->getResolutionWidth()	."<br>";
		echo "Resolution Y: "	.$this->getResolutionHeight()	."<br>";
		echo "Chunk X: "		.$this->getChunksizeWidth()		."<br>";
		echo "Chunk Y: "		.$this->getChunksizeHeight()	."<br>";

		echo "colorsCollection:<br> <pre>";
		print_r($this->getColorsCollection());
		echo "</pre>";
		echo '<div><hr></div>';
	}

    /**
     * public function showPossibleChunkSize()
     * @echo
    */
	public function showPossibleChunkSize(){
        $imagewidth 	= $this->getResolutionWidth();
        $imageheight 	= $this->getResolutionHeight();

        echo 'Possible sizes width:';
        for($position = 0; $position <= $imagewidth; $position++){
        	if($position != 0){
        		if($imagewidth % $position == 0){
        			echo $position."<br>";
        		}
        	}
        }
        echo '<hr>';

        echo 'Possible sizes height:';
        for($position = 0; $position <= $imageheight; $position++){
        	if($position != 0){
        		if($imageheight % $position == 0){
        			echo $position."<br>";
        		}
        	}
        }
        echo '<hr>';
    }

    /**
     * public function createImage()
     * 
    */
	public function createImage(){

        $imageTargetResource = @ImageCreate($this->getResolutionWidth(), $this->getResolutionHeight());
        $imageTargetColors = $this->getColorsCollection();

        $actRow = 0;

        foreach ($imageTargetColors as $colorKey => $colorValue) {

            $imageColorExploded = explode(',', $colorValue);

            $colorInt = imagecolorallocate ( $imageTargetResource , $imageColorExploded[0] , $imageColorExploded[1], $imageColorExploded[2] );

            $x1 = $colorKey * $this->getChunksizeWidth();
            $y1 = $actRow * $this->getChunksizeHeight();

            $x2 = ($colorKey * $this->getChunksizeWidth()) + $this->getChunksizeWidth();
            $y2 = ($actRow * $this->getChunksizeHeight()) + $this->getChunksizeHeight();

            imagefilledrectangle($imageTargetResource, $x1, $y1, $x2, $y2, $colorInt);

            if($colorKey % $this->getResultImageCols() == 0){
                $actRow++;
            }
        }

        imagejpeg($imageTargetResource, $this->getResultimagePath(), 100);
        imagedestroy($imageTargetResource);
	}

    /**
     * public function rawImage()
     * @echo
    */
	public function rawImage(){
		echo '<img src="'.$this->getImagePath().'" style="width:40vw;" />';
		echo '<br>';
	}

    /**
     * public function renderImage()
     * @echo
    */
	public function renderImage(){
		$colors 		= $this->getColorsCollection();
		
		$width 			= $this->getChunksizeWidth() / 5;
		$height 		= $this->getChunksizeHeight() / 5;

		$source_width 	= $this->getResolutionWidth();
		$source_height 	= $this->getResolutionHeight();

		$stopper 		= $this->getResolutionWidth() / $this->getChunksizeWidth();

		foreach ($colors as $key => $value) {
			if ((($key % $stopper) == 0) && ($key != 0) ){
				echo '<br>';
			}

			echo '<span class="num-'.$key.'" style="display:inline-block; height:'.$height.'px;width:'.$width.'px;background-color:RGB('.$value.')"></span>';
		}
	}

    /**
     * public function createChunks()
     * 
    */
	public function createChunks(){

		$width 			= $this->getChunksizeWidth();
		$height 		= $this->getChunksizeHeight();

		$source 		= $this->getImageResource();
		$source_width 	= $this->getResolutionWidth();
		$source_height 	= $this->getResolutionHeight();

		$this->setTempImageWidth($source_width / $width);
		$this->setTempImageHeight($source_height / $height);

		for( $row = 0; $row < $source_height / $height; $row++)
		{
		    for( $col = 0; $col < $source_width / $width; $col++ )
		    {
		        $im = @imagecreatetruecolor( $width, $height );
		        imagecopyresized( $im, $source, 0, 0, $col * $width, $row * $height, $width, $height, $width, $height );

		        $this->getDominateColor($im);
		        imagedestroy( $im );
		    }
		}

		$this->setResultImageCols($col);
		$this->setResultImageRows($row);
	}

    /**
     * public function setResolution()
     * 
    */
	public function setResolution(){
		$width 	= imagesx($this->getImageResource());
		$height = imagesy($this->getImageResource()); 
		
		$this->setResolutionWidth($width);
		$this->setResolutionHeight($height);
	}

    /**
     * public function getDominateColor([Resource])
     * @resource
    */
	public function getDominateColor($image){

		$i = $image;
		
		$rTotal = 0;
		$gTotal = 0;
		$bTotal = 0;
		$total  = 0;

		for ($x=0;$x<imagesx($i);$x++) {
		    for ($y=0;$y<imagesy($i);$y++) {
		        $rgb = imagecolorat($i,$x,$y);
		        $r   = ($rgb >> 16) & 0xFF;
		        $g   = ($rgb >>  8) & 0xFF;
		        $b   = $rgb & 0xFF;
		        $rTotal += $r;
		        $gTotal += $g;
		        $bTotal += $b;
		        $total++;
		    }
		}

		$rAverage = round($rTotal/$total);
		$gAverage = round($gTotal/$total);
		$bAverage = round($bTotal/$total);

		$rgb = $rAverage.",".$gAverage.",".$bAverage;
		$this->setColorsCollection($rgb);
	}

    /**
     * @return mixed
     */
    public function getImageResource()
    {
        return $this->imageResource;
    }

    /**
     * @param mixed $imageResource
     *
     * @return self
     */
    public function setImageResource($imageResource)
    {
        $this->imageResource = $imageResource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResolutionWidth()
    {
        return $this->resolutionWidth;
    }

    /**
     * @param mixed $resolutionWidth
     *
     * @return self
     */
    public function setResolutionWidth($resolutionWidth)
    {
        $this->resolutionWidth = $resolutionWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResolutionHeight()
    {
        return $this->resolutionHeight;
    }

    /**
     * @param mixed $resolutionHeight
     *
     * @return self
     */
    public function setResolutionHeight($resolutionHeight)
    {
        $this->resolutionHeight = $resolutionHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChunksizeWidth()
    {
        return $this->chunksizeWidth;
    }

    /**
     * @param mixed $chunkSizeWidth
     *
     * @return self
     */
    public function setChunksizeWidth($chunksizeWidth)
    {
        $this->chunksizeWidth = $chunksizeWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChunksizeHeight()
    {
        return $this->chunksizeHeight;
    }

    /**
     * @param mixed $chunkSizeHeight
     *
     * @return self
     */
    public function setChunksizeHeight($chunksizeHeight)
    {
        $this->chunksizeHeight = $chunksizeHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param mixed $imagePath
     *
     * @return self
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColorsCollection()
    {
        return $this->colorsCollection;
    }

    /**
     * @param mixed $colorsCollection
     *
     * @return self
     */
    public function setColorsCollection($colorsCollection)
    {
        $this->colorsCollection[] = $colorsCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempImageWidth()
    {
        return $this->tempImageWidth;
    }

    /**
     * @param mixed $tempImageWidth
     *
     * @return self
     */
    public function setTempImageWidth($tempImageWidth)
    {
        $this->tempImageWidth = $tempImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempImageHeight()
    {
        return $this->tempImageHeight;
    }

    /**
     * @param mixed $tempImageHeight
     *
     * @return self
     */
    public function setTempImageHeight($tempImageHeight)
    {
        $this->tempImageHeight = $tempImageHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageWidth()
    {
        return $this->resultImageWidth;
    }

    /**
     * @param mixed $resultImageWidth
     *
     * @return self
     */
    public function setResultImageWidth($resultImageWidth)
    {
        $this->resultImageWidth = $resultImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageCols()
    {
        return $this->resultImageCols;
    }

    /**
     * @param mixed $resultImageCols
     *
     * @return self
     */
    public function setResultImageCols($resultImageCols)
    {
        $this->resultImageCols = $resultImageCols;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageRows()
    {
        return $this->resultImageRows;
    }

    /**
     * @param mixed $resultImageRows
     *
     * @return self
     */
    public function setResultImageRows($resultImageRows)
    {
        $this->resultImageRows = $resultImageRows;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultimagePath()
    {
        return $this->resultimagePath;
    }

    /**
     * @param mixed $resultimagePath
     *
     * @return self
     */
    public function setResultimagePath($resultimagePath)
    {
        $this->resultimagePath = $resultimagePath;

        return $this;
    }
}


?>