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
	protected $chunkSizeWidth;
    /**
     * number of pixel height for chunk
     * @integer
    */
	protected $chunkSizeHeight;
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
		$this->chunkSizeWidth 	= $chunkwidth;
		$this->chunkSizeHeight 	= $chunkheight;
		$this->resultimagePath 	= $output;
	}

    /**
     * public function debug()
     * @echo
    */
	public function debug(){
		echo "Imagepath: "		.$this->getimagePath()			."<br>";
		echo "Resolution X: "	.$this->getresolutionWidth()	."<br>";
		echo "Resolution Y: "	.$this->getresolutionHeight()	."<br>";
		echo "Chunk X: "		.$this->getchunkSizeWidth()		."<br>";
		echo "Chunk Y: "		.$this->getchunkSizeHeight()	."<br>";

		echo "colorsCollection:<br> <pre>";
		print_r($this->getcolorsCollection());
		echo "</pre>";
		echo '<div><hr></div>';
	}

    /**
     * public function showPossibleChunkSize()
     * @echo
    */
	public function showPossibleChunkSize(){
        $imagewidth 	= $this->getresolutionWidth();
        $imageheight 	= $this->getresolutionHeight();

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
     * //TODO
    */
	public function createImage(){

		//Todo
	}

    /**
     * public function rawImage()
     * @echo
    */
	public function rawImage(){
		echo '<img src="'.$this->getimagePath().'" style="width:40vw;" />';
		echo '<br>';
	}

    /**
     * public function renderImage()
     * @echo
    */
	public function renderImage(){
		$colors 		= $this->getcolorsCollection();
		
		$width 			= $this->getchunkSizeWidth() / 5;
		$height 		= $this->getChunksizeHeight() / 5;

		$source_width 	= $this->getresolutionWidth();
		$source_height 	= $this->getresolutionHeight();

		$stopper 		= $this->getresolutionWidth() / $this->getchunkSizeWidth();

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

		$width 			= $this->getchunkSizeWidth();
		$height 		= $this->getChunksizeHeight();

		$source 		= $this->getimageResource();
		$source_width 	= $this->getresolutionWidth();
		$source_height 	= $this->getresolutionHeight();

		$this->settempImageWidth($source_width / $width);
		$this->settempImageHeight($source_height / $height);

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

		$this->setresultImageCols($col);
		$this->setresultImageRows($row);
	}

    /**
     * public function setResolution()
     * 
    */
	public function setResolution(){
		$width 	= imagesx($this->getimageResource());
		$height = imagesy($this->getimageResource()); 
		
		$this->setresolutionWidth($width);
		$this->setresolutionHeight($height);
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
		$this->setcolorsCollection($rgb);
	}

    /**
     * @return mixed
     */
    public function getimageResource()
    {
        return $this->imageResource;
    }

    /**
     * @param mixed $imageResource
     *
     * @return self
     */
    public function setimageResource($imageResource)
    {
        $this->imageResource = $imageResource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresolutionWidth()
    {
        return $this->resolutionWidth;
    }

    /**
     * @param mixed $resolutionWidth
     *
     * @return self
     */
    public function setresolutionWidth($resolutionWidth)
    {
        $this->resolutionWidth = $resolutionWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresolutionHeight()
    {
        return $this->resolutionHeight;
    }

    /**
     * @param mixed $resolutionHeight
     *
     * @return self
     */
    public function setresolutionHeight($resolutionHeight)
    {
        $this->resolutionHeight = $resolutionHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getchunkSizeWidth()
    {
        return $this->chunkSizeWidth;
    }

    /**
     * @param mixed $chunkSizeWidth
     *
     * @return self
     */
    public function setchunkSizeWidth($chunkSizeWidth)
    {
        $this->chunkSizeWidth = $chunkSizeWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getchunkSizeHeight()
    {
        return $this->chunkSizeHeight;
    }

    /**
     * @param mixed $chunkSizeHeight
     *
     * @return self
     */
    public function setchunkSizeHeight($chunkSizeHeight)
    {
        $this->chunkSizeHeight = $chunkSizeHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getimagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param mixed $imagePath
     *
     * @return self
     */
    public function setimagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getcolorsCollection()
    {
        return $this->colorsCollection;
    }

    /**
     * @param mixed $colorsCollection
     *
     * @return self
     */
    public function setcolorsCollection($colorsCollection)
    {
        $this->colorsCollection[] = $colorsCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function gettempImageWidth()
    {
        return $this->tempImageWidth;
    }

    /**
     * @param mixed $tempImageWidth
     *
     * @return self
     */
    public function settempImageWidth($tempImageWidth)
    {
        $this->tempImageWidth = $tempImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function gettempImageHeight()
    {
        return $this->tempImageHeight;
    }

    /**
     * @param mixed $tempImageHeight
     *
     * @return self
     */
    public function settempImageHeight($tempImageHeight)
    {
        $this->tempImageHeight = $tempImageHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresultImageWidth()
    {
        return $this->resultImageWidth;
    }

    /**
     * @param mixed $resultImageWidth
     *
     * @return self
     */
    public function setresultImageWidth($resultImageWidth)
    {
        $this->resultImageWidth = $resultImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresultImageCols()
    {
        return $this->resultImageCols;
    }

    /**
     * @param mixed $resultImageCols
     *
     * @return self
     */
    public function setresultImageCols($resultImageCols)
    {
        $this->resultImageCols = $resultImageCols;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresultImageRows()
    {
        return $this->resultImageRows;
    }

    /**
     * @param mixed $resultImageRows
     *
     * @return self
     */
    public function setresultImageRows($resultImageRows)
    {
        $this->resultImageRows = $resultImageRows;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getresultimagePath()
    {
        return $this->resultimagePath;
    }

    /**
     * @param mixed $resultimagePath
     *
     * @return self
     */
    public function setresultimagePath($resultimagePath)
    {
        $this->resultimagePath = $resultimagePath;

        return $this;
    }
}


?>