<?php
namespace Snake\Libs\Image;
/**
 * Use Imagick to manipulate images. 
 */
class ImageImagick{
    private $imagickObj;
    private $info; //'width','height','extension'

    public function __construct(){
        $this->imagickObj = FALSE;
        $this->info = FALSE;
    }

    public function load($filepath) {
        if (is_file($filepath)) {
            try {
                $this->imagickObj = new \Imagick();
                $this->imagickObj->readImage($filepath);
                $this->info = $this->getImageInfo();
            }
            catch (\ImagickException $e) {
                unset($this->imagickObj);
                $this->imagickObj = FALSE;
                $this->info = FALSE;
            }
        }
    }

    public function getImageInfo() {
        if ( FALSE === $this->imagickObj )
            return FALSE;
        try {
            $this->imagickObj->setImageIndex(0);
            $info['width'] = $this->imagickObj->getImageWidth();
            $info['height'] =  $this->imagickObj->getImageHeight();
            $info['extension'] = strtolower($this->imagickObj->getImageFormat());
            $info['extension'] = str_replace('jpeg','jpg',$info['extension']);
            return $info;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }
    
    public function isLoaded() {
        return !($this->imagickObj === FALSE);
    }

    public function setExtension($ext) {
        $this->imagickObj->setImageFormat($ext);  
        $this->info['extension'] = $ext;
    }

    public function getExt() {
        return $this->info['extension'];
    }
    public function getWidth() {
        return $this->info['width'];
    }
    public function getHeight() {
        return $this->info['height'];
    }
    public function resizeTo($width, $height) {
        if (FALSE === $this->imagickObj) return FALSE;

        try {
             $this->imagickObj->scaleImage($width,$height);

            $this->info['width'] = $width;
            $this->info['height'] = $height;
            return TRUE;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }

    public function getContent($extension = NULL,$jpgQuality = 90,$keepAnimation = FALSE){
        
        is_null($extension) && $extension = $this->info['extension'];
        is_null($jpgQuality) && $jpgQuality = 90;
        try {
            $this->imagickObj->setImageFormat($extension);
            if($extension == 'jpg' || $extension == 'jpeg'){
                $this->imagickObj->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $this->imagickObj->setImageCompressionQuality($jpgQuality);
            }
	    // Set the orientation to 1 or 0, for bug #1626
            $orientation = $this->imagickObj->getImageOrientation();
            if ($orientation != \Imagick::ORIENTATION_TOPLEFT
                && $orientation != \Imagick::ORIENTATION_UNDEFINED)
                $this->imagickObj->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
            if($keepAnimation){
                $bytes = $this->imagickObj->getImagesBlob();
            }
            else {
                $bytes = $this->imagickObj->getImageBlob();
            }
            return $bytes;
        } 
        catch (\ImagickException $e){
            return FALSE;
        }
    }

    public function getSingleContent(){
        try {
            $bytes = $this->imagickObj->getImageBlob();
            return $bytes;
        } 
        catch (\ImagickException $e){
            return FALSE;
        }
    }
    public function loadFromMem($bytes,$ext){
        if(!is_string($bytes)){
            return FALSE;
        }

        unset($this->imagickObj);
        $this->imagickObj = FALSE;
        $this->info = FALSE;
        try {
            $this->imagickObj = new \Imagick();
            $this->imagickObj->readImageBlob($bytes);
	    $this->info = $this->getImageInfo();
            /* $this->info['width'] = $this->imagickObj->getImageWidth(); */
            /* $this->info['height'] = $this->imagickObj->getImageHeight(); */
            /* $this->info['extension'] = strtolower($ext); */
            return TRUE;
        }
        catch (\ImagickException $e) {
            unset($this->imagickObj);
            $this->imagickObj = FALSE;
            $this->info = FALSE;
            return FALSE;
        }
    }

    public function cloneMyself(){
        if ( FALSE === $this->imagickObj )
            return FALSE;

        try{
            $imgNew = new ImageImagick();
            $imgNew->imagickObj = $this->imagickObj->clone();
            $imgNew->info = $this->info;
            return $imgNew;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }

    public function cropTo($x,$y,$width,$height){
        return $this->crop($x,$y,$width,$height);
    }
    public function crop($x,$y,$width,$height){
        try {
            $this->imagickObj->cropImage($width, $height, $x, $y);
            //$str = $width . "X" . $height . "+0+0";
            //$this->imagickObj->resetImagePage($str);
            $this->info['width'] = $width;
            $this->info['height'] = $height;
            return TRUE;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }

    static public function calcScaleImgSize($oWidth,$oHeight,
                        $width=NULL, $height=NULL,$upscale=FALSE){
        $result = array();
        if(empty($oHeight) || empty($oWidth)){
            return FALSE;
        }
        $aspect = $oHeight / $oWidth;
        if ($upscale) {
            // Set width/height according to aspect ratio if either is empty.
            $width = !empty($width) ? $width : $height / $aspect;
            $height = !empty($height) ? $height : $width / $aspect;
        }
        else {
            // Set impossibly large values if the width and height aren't set.
            $width = !empty($width) ? $width : 9999999;
            $height = !empty($height) ? $height : 9999999;

            // Don't scale up.
            if (round($width) >= $oWidth && round($height) >= $oHeight) {
                $result['width'] = $oWidth;
                $result['height'] = $oHeight;
                return $result;;
            }
        }

        if ($aspect < $height / $width) {
            $height = $width * $aspect;
        }
        else {
            $width = $height / $aspect;
        }
        $result['width'] = (int)$width;
        $result['height'] = (int)$height;
        return $result;
    }

    public function createScaleImg($width = NULL, $height = NULL, $upscale = FALSE) {
        $sizeInfo = self::calcScaleImgSize($this->info['width'],$this->info['height'],
            $width,$height,$upscale);
        return $this->createResizeImg($sizeInfo['width'],$sizeInfo['height']); 
    }

    public function createResizeImg($width, $height) {
        if (FALSE === $this->imagickObj) return FALSE;

        try {
            $imgNew = new ImageImagick();
            $imgNew->imagickObj = $this->imagickObj->clone();
            // $imgNew->imagickObj->sampleImage($width,$height);
             $imgNew->imagickObj->scaleImage($width,$height);
             //$imgNew->imagickObj->resizeImage($width,$height,imagick::FILTER_BOX,1);
             //$imgNew->imagickObj->resizeImage($width,$height,imagick::FILTER_BOX,1);
             //$imgNew->imagickObj->adaptiveResizeImage($width,$height);
            // $imgNew->imagickObj->thumbnailImage($width,$height);
            // $imgNew->imagickObj->setImageExtent($width,$height);

            $imgNew->info['width'] = $width;
            $imgNew->info['height'] = $height;
            $imgNew->info['extension'] = $this->info['extension'];
            return $imgNew;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }

    public function isSameSize(){
        if (FALSE === $this->imagickObj) return FALSE;
        
        try{
            $this->imagickObj->setImageIndex(0);
            $width0 = $this->info['width'];
            $height0 = $this->info['height']; 
            do {
                $this->imagickObj->sampleImage($width,$height);
                $widthIng = $this->imagickObj->getImageWidth();
                $heightIng = $this->imagickObj->getImageHeight();
                if ( ( $width0 !== $widthIng ) || ( $height0 !== $heightIng) )
                    return FALSE;
            }
            while( $imgNew->imagickObj->nextImage() );
            return TRUE;
        }
        catch (\ImagickException $e) {
            return FALSE;
        }
    }

    public function reduceToSingleImg(){
        try {
            $bytes = $this->getSingleContent();
            if (FALSE === $bytes)
                return FALSE;
            unset($this->imagickObj);
            $this->imagickObj = new \Imagick();
            $this->imagickObj->readImageBlob($bytes);
            return TRUE;
        } 
        catch (\ImagickException $e){
            return FALSE;
        }
    }
    public function changeHSB($hue,$saturation,$bright){
        return $this->imagickObj->modulateImage($bright*100,$saturation*100,$hue*100);
    }
    public function sharpenEx($amount,$radius,$threshold){
        return $this->imagickObj->unsharpMaskImage($radius,$radius,$amount/100.0,$threshold/255.0);
    }
    public function contrast($amount) {
        return $this->imagickObj->contrastImage($amount);
    }
    public function addWatermark($imgLogo,$x,$y,$transparancy = 0,$logoTransparentColor = NULL) {
        return $this->imagickObj->compositeImage($imgLogo->imagickObj,\Imagick::COMPOSITE_DEFAULT,$x,$y);
    }

}

