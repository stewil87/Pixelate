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

class pixelate {
    /**
     * relative path to custom image file inc. filename and extension
     * @string
    */
	protected $ImagePath;
    /**
     * Readed Image filestream
     * @resource
    */
	protected $ImageResource;
    /**
     * X direction Resolution of loaded image
     * @integer
    */
	protected $ResolutionWidth;
    /**
     * Y direction Resolution of loaded image
     * @integer
    */
	protected $ResolutionHeight;
    /**
     * number of pixel width for chunk
     * @integer
    */
	protected $ChunkSizeWidth;
    /**
     * number of pixel height for chunk
     * @integer
    */
	protected $ChunkSizeHeight;
    /**
     * average color collection of every chunk
     * @array
    */
	protected $ColorsCollection = array();
    /**
     * Width of Image resolution width / chunksize width
     * @float
    */
	protected $TempImageWidth;
    /**
     * Width of Image resolution height / chunksize height
     * @float
    */
	protected $TempImageHeight;
    /**
     * Width of Image in pixel
     * @int
    */
	protected $ResultImageWidth;
    /**
     * number of chunks per row
     * @integer
    */
	protected $ResultImageCols;
    /**
     * number of chunk rows
     * @integer
    */
	protected $ResultImageRows;
    /**
     * future result image path
     * @string
    */
	protected $ResultImagePath;

    /**
     * public function new class([string],[string],[int],[int])
    */
	public function __construct($path, $output, $chunkwidth, $chunkheight){
		$this->ImageResource 	= @ImageCreateFromJPEG($path);
		$this->ImagePath 		= $path;
		$this->ChunkSizeWidth 	= $chunkwidth;
		$this->ChunkSizeHeight 	= $chunkheight;
		$this->ResultImagePath 	= $output;
	}

    /**
     * public function debug()
     * @echo
    */
	public function debug(){
		echo "Imagepath: "		.$this->getImagePath()			."<br>";
		echo "Resolution X: "	.$this->getResolutionWidth()	."<br>";
		echo "Resolution Y: "	.$this->getResolutionHeight()	."<br>";
		echo "Chunk X: "		.$this->getChunkSizeWidth()		."<br>";
		echo "Chunk Y: "		.$this->getChunkSizeHeight()	."<br>";

		echo "ColorsCollection:<br> <pre>";
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
		echo '<img src="'.$this->getImagePath().'" style="width:40vw;" />';
		echo '<br>';
	}

    /**
     * public function renderImage()
     * @echo
    */
	public function renderImage(){
		$colors 		= $this->getColorsCollection();
		
		$width 			= $this->getChunkSizeWidth() / 5;
		$height 		= $this->getChunksizeHeight() / 5;

		$source_width 	= $this->getResolutionWidth();
		$source_height 	= $this->getResolutionHeight();

		$stopper 		= $this->getResolutionWidth() / $this->getChunkSizeWidth();

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

		$width 			= $this->getChunkSizeWidth();
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
        return $this->ImageResource;
    }

    /**
     * @param mixed $ImageResource
     *
     * @return self
     */
    public function setImageResource($ImageResource)
    {
        $this->ImageResource = $ImageResource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResolutionWidth()
    {
        return $this->ResolutionWidth;
    }

    /**
     * @param mixed $ResolutionWidth
     *
     * @return self
     */
    public function setResolutionWidth($ResolutionWidth)
    {
        $this->ResolutionWidth = $ResolutionWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResolutionHeight()
    {
        return $this->ResolutionHeight;
    }

    /**
     * @param mixed $ResolutionHeight
     *
     * @return self
     */
    public function setResolutionHeight($ResolutionHeight)
    {
        $this->ResolutionHeight = $ResolutionHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChunkSizeWidth()
    {
        return $this->ChunkSizeWidth;
    }

    /**
     * @param mixed $ChunkSizeWidth
     *
     * @return self
     */
    public function setChunkSizeWidth($ChunkSizeWidth)
    {
        $this->ChunkSizeWidth = $ChunkSizeWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChunkSizeHeight()
    {
        return $this->ChunkSizeHeight;
    }

    /**
     * @param mixed $ChunkSizeHeight
     *
     * @return self
     */
    public function setChunkSizeHeight($ChunkSizeHeight)
    {
        $this->ChunkSizeHeight = $ChunkSizeHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->ImagePath;
    }

    /**
     * @param mixed $ImagePath
     *
     * @return self
     */
    public function setImagePath($ImagePath)
    {
        $this->ImagePath = $ImagePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColorsCollection()
    {
        return $this->ColorsCollection;
    }

    /**
     * @param mixed $ColorsCollection
     *
     * @return self
     */
    public function setColorsCollection($ColorsCollection)
    {
        $this->ColorsCollection[] = $ColorsCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempImageWidth()
    {
        return $this->TempImageWidth;
    }

    /**
     * @param mixed $TempImageWidth
     *
     * @return self
     */
    public function setTempImageWidth($TempImageWidth)
    {
        $this->TempImageWidth = $TempImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempImageHeight()
    {
        return $this->TempImageHeight;
    }

    /**
     * @param mixed $TempImageHeight
     *
     * @return self
     */
    public function setTempImageHeight($TempImageHeight)
    {
        $this->TempImageHeight = $TempImageHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageWidth()
    {
        return $this->ResultImageWidth;
    }

    /**
     * @param mixed $ResultImageWidth
     *
     * @return self
     */
    public function setResultImageWidth($ResultImageWidth)
    {
        $this->ResultImageWidth = $ResultImageWidth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageCols()
    {
        return $this->ResultImageCols;
    }

    /**
     * @param mixed $ResultImageCols
     *
     * @return self
     */
    public function setResultImageCols($ResultImageCols)
    {
        $this->ResultImageCols = $ResultImageCols;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImageRows()
    {
        return $this->ResultImageRows;
    }

    /**
     * @param mixed $ResultImageRows
     *
     * @return self
     */
    public function setResultImageRows($ResultImageRows)
    {
        $this->ResultImageRows = $ResultImageRows;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultImagePath()
    {
        return $this->ResultImagePath;
    }

    /**
     * @param mixed $ResultImagePath
     *
     * @return self
     */
    public function setResultImagePath($ResultImagePath)
    {
        $this->ResultImagePath = $ResultImagePath;

        return $this;
    }
}


?>