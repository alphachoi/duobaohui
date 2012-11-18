<?php
namespace Snake\Libs\Image;

/**
 * Use GD to manipulate images.
 */
class Image {
    var $resource;
    var $info;//'width','height','extension'

    public static $jpeg_quality = 90;

    public function __construct() {
        $this->resource = FALSE;
        $this->info = FALSE;
    }

    public function load($filepath) {
        if (is_file($filepath)) {
            $this->info = self::getImageInfo($filepath);
            $ext = str_replace('jpg', 'jpeg', $this->info['extension']);
            $function = 'imagecreatefrom' . $ext;
            if (function_exists($function)) {
                $this->resource = $function($filepath);
            }
        }
    }

    public function attach($imgRes,$ext){
        if($this->resource !== FALSE){
            imagedestroy($this->resource);
        }
        $this->resource = FALSE;
        $this->info = FALSE;
        $this->resource = $imgRes;
        if(is_resource($this->resource)){
            $this->info['width'] = imagesx($this->resource);
            $this->info['height'] = imagesy($this->resource);
            $this->info['extension'] = strtolower($ext);
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    public function loadFromMem($bytes,$ext){
        if(!is_string($bytes)){
            return FALSE;
        }
        $this->resource = FALSE;
        $this->info = FALSE;
        $this->resource = imagecreatefromstring($bytes);
        if(is_resource($this->resource)){
            $this->info['width'] = imagesx($this->resource);
            $this->info['height'] = imagesy($this->resource);
            $this->info['extension'] = strtolower($ext);
            return TRUE;
        }
        else {
            $this->resource = FALSE;
            return FALSE;
        }

    }

    public function getResource(){
        return $this->resource;
    }

    public function cloneMyself(){
        $width = $this->info['width'];
        $height = $this->info['height'];
        $newRes = $this->createTmp($width,$height);
        if(imagecopy($newRes,$this->resource,0,0,0,0,$width,$height) === TRUE){
            $imgNew = new Image();
            $imgNew->resource = $newRes;
            $imgNew->info = $this->info;
            return $imgNew;
        } 
        else {
            return FALSE;
        }

    }

    public function isLoaded() {
        return !($this->resource === FALSE);
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

    /**
     * Save file into $destination directory. By default using oroginal file
     * name if $newName is not specified.
     * Note this file does not check if $destination is exsitent and
     * writable!
     *
     * $extension could be used to force saving format.
     */
    public function saveTo($destination, $extension = NULL) {
        $bytes = $this->getContent($extension);
        if(is_bool($bytes)){
            return FALSE;
        }
        $wrote_len = file_put_contents($destination,$bytes);
        if(is_bool($wrote_len)){
            return FALSE;
        }
        return TRUE;
   }
    /**
     * 返回二进制内容.
     * @param ext : 字符串,jpg,png等类型.
     * @return : 成功返回二进制字符串,失败返回bool型的FALSE.
     */
    public function getContent($extension = NULL,$jpgQuality = NULL){
        is_null($extension) && $extension = $this->info['extension'];
        is_null($jpgQuality) && $jpgQuality = self::$jpeg_quality;
        $extension = str_replace('jpg', 'jpeg', $extension);

        $function = 'image' . $extension;
        if (!function_exists($function)) {
            return FALSE;
        }

        $ret = FALSE;


        ob_start();
        if ($extension == 'jpeg') {
            $ret =  $function($this->resource, NULL, $jpgQuality);
        }
        else {
            // Always save PNG images with full transparency.
            if ($extension == 'png') {
                imagealphablending($this->resource, FALSE);
                imagesavealpha($this->resource, TRUE);
            }
            $ret =  $function($this->resource, NULL);
        }

        $bytes = ob_get_contents();
        ob_end_clean();
        return $bytes;
    }

    
    public function resizeTo($width, $height) {
        if (FALSE === $this->resource) return FALSE;

        $resized = $this->createTmp($width, $height);

        if (!imagecopyresampled($resized, $this->resource, 0, 0, 0, 0, $width, $height, $this->info['width'], $this->info['height'])) {
            return FALSE;
        }
        imagedestroy($this->resource);
        $this->resource = $resized;
        $this->info['width'] = $width;
        $this->info['height'] = $height;
        //$this->sharpen();
        return TRUE;
    }

    public function createResizeImg($width,$height){
        if (FALSE === $this->resource) return FALSE;

        $resized = $this->createTmp($width, $height);

        if (!imagecopyresampled($resized, $this->resource, 0, 0, 0, 0, $width,
                      $height, $this->info['width'], $this->info['height'])) {
            return FALSE;
        }
        $imgNew = new Image();
        $imgNew->resource = $resized;
        $imgNew->info = $this->info;
        $imgNew->info['width'] = $width;
        $imgNew->info['height'] = $height;
        //$imgNew->sharpen();
        return $imgNew;

    }

    public function cropTo($x, $y, $width, $height) {
        $cropped = $this->createTmp($width, $height);

        if (!imagecopyresampled($cropped, $this->resource, 0, 0, $x, $y, $width, $height, $width, $height)) {
            return FALSE;
        }

        imagedestroy($this->resource);
        $this->resource = $cropped;
        $this->info['width'] = imagesx($this->resource);
        $this->info['height'] = imagesy($this->resource);
        return TRUE;
    }
    public function crop($x,$y,$width,$height){
        return $this->cropTo($x,$y,$width,$height);
    }
	
    /*
     * 从(x,y)起裁剪(width,height)的图片并缩放到scale倍,不改变源图.
     * @param x: 起点x坐标.
     * @param y: 起点y坐标.
     * @param width: 新图宽度.
     * @param height: 新图高度.
     * @return 新图片
     */
	 public function cropToScale($x, $y, $width, $height, $scale) {
		$new_width = $width * $scale ;
		$new_height = $height * $scale;
		$cropped = $this->createTmp($new_width, $new_height);
        if (!imagecopyresampled($cropped, $this->resource, 0, 0, $x, $y, $new_width, $new_height, $width, $height)) {
            return FALSE;
        }
		imagedestroy($this->resource);
        $this->resource = $cropped;
        $this->info['width'] = imagesx($this->resource);
        $this->info['height'] = imagesy($this->resource);
        return TRUE;
	 }

    public function scaleTo($width = NULL, $height = NULL, $upscale = FALSE) {
        $aspect = $this->info['height'] / $this->info['width'];

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
            if (round($width) >= $this->info['width'] && round($height) >= $this->info['height']) {
                return TRUE;
            }
        }

        if ($aspect < $height / $width) {
            $height = $width * $aspect;
        }
        else {
            $width = $height / $aspect;
        }

        return $this->resizeTo($width, $height); 
    }

    public static function calcScaleImgSize($oWidth, $oHeight, $width=NULL, $height=NULL,$upscale=FALSE) {
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
        $sizeInfo = self::calcScaleImgSize($this->info['width'], $this->info['height'], $width,$height,$upscale);
        return $this->createResizeImg($sizeInfo['width'],$sizeInfo['height']); 
    }

    //public function sharpen() {
    //    $amount = 50;
    //    $radius = 1;
    //    $threshold = 1;

    //    $this->resource = self::unsharpMask($this->resource, $amount, $radius, $threshold);
    //}

    public function sharpenEx($amount,$radius,$threshold){
        $this->resource = self::unsharpMask($this->resource, $amount, $radius, $threshold);
    }

    public function createTmp($width, $height) {
        $res = imagecreatetruecolor($width, $height);
        if($res === FALSE){
            return FALSE;
        }
        switch ($this->info['extension']) {
            case 'gif':
                // Grab transparent color index from image resource.
                $transparent = imagecolortransparent($this->resource);
                $palletsize = imagecolorstotal($this->resource); //增加调色范围判
                if ($transparent >= 0 && $transparent < $palletsize) {
                    // The original must have a transparent color, allocate to the new image.
                    $transparent_color = imagecolorsforindex($this->resource, $transparent);
                    $transparent = imagecolorallocate($res, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);

                    // Flood with our new transparent color.
                    imagefill($res, 0, 0, $transparent);
                    imagecolortransparent($res, $transparent);
                }
                break;

            case 'png':
                imagealphablending($res, FALSE);
                $transparency = imagecolorallocatealpha($res, 0, 0, 0, 127);
                imagefill($res, 0, 0, $transparency);
                imagealphablending($res, TRUE);
                imagesavealpha($res, TRUE);
                break;

            default:
                imagefill($res, 0, 0, imagecolorallocate($res, 255, 255, 255));
                break;
        }

        return $res;
    }

    static function getImageInfo($filepath) {
        $details = FALSE;
        $data = getimagesize($filepath);

        if (isset($data) && is_array($data)) {
            $extensions = array(
                '1' => 'gif',
                '2' => 'jpg',
                '3' => 'png',
                '6' => 'bmp',
            );
            $extension = isset($extensions[$data[2]]) ?   $extensions[$data[2]] : '';
            $details = array(
                'width' => $data[0], 
                'height' => $data[1], 
                'extension' => $extension, 
            );
        }

        return $details; 
    }

    /**
     * Codes from:
     * http://vikjavev.no/computing/ump.php
     */
    static function unsharpMask($img, $amount, $radius, $threshold)    { 
        // $img is an image that is already created within php using 
        // imgcreatetruecolor. No url! $img must be a truecolor image. 

        // Attempt to calibrate the parameters to Photoshop: 
        if ($amount > 500)    $amount = 500; 
        $amount = $amount * 0.016; 
        if ($radius > 50)    $radius = 50; 
        $radius = $radius * 2; 
        if ($threshold > 255)    $threshold = 255; 
         
        $radius = abs(round($radius));     // Only integers make sense. 
        if ($radius == 0) { 
            return $img; imagedestroy($img); break;        } 
        $w = imagesx($img); $h = imagesy($img); 
        $imgCanvas = imagecreatetruecolor($w, $h); 
        $imgBlur = imagecreatetruecolor($w, $h); 
         

        // Gaussian blur matrix: 
        //                         
        //    1    2    1         
        //    2    4    2         
        //    1    2    1         
        //                         
        ////////////////////////////////////////////////// 
             

        if (function_exists('imageconvolution')) { // PHP >= 5.1  
                $matrix = array(  
                array( 1, 2, 1 ),  
                array( 2, 4, 2 ),  
                array( 1, 2, 1 )  
            );  
            imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h); 
            imageconvolution($imgBlur, $matrix, 16, 0);  
        }  
        else {  

        // Move copies of the image around one pixel at the time and merge them with weight 
        // according to the matrix. The same matrix is simply repeated for higher radii. 
            for ($i = 0; $i < $radius; $i++)    { 
                imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left 
                imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right 
                imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center 
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 

                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up 
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down 
            } 
        } 

        if($threshold>0){ 
            // Calculate the difference between the blurred pixels and the original 
            // and set the pixels 
            for ($x = 0; $x < $w-1; $x++)    { // each row
                for ($y = 0; $y < $h; $y++)    { // each pixel 
                         
                    $rgbOrig = ImageColorAt($img, $x, $y); 
                    $rOrig = (($rgbOrig >> 16) & 0xFF); 
                    $gOrig = (($rgbOrig >> 8) & 0xFF); 
                    $bOrig = ($rgbOrig & 0xFF); 
                     
                    $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                     
                    $rBlur = (($rgbBlur >> 16) & 0xFF); 
                    $gBlur = (($rgbBlur >> 8) & 0xFF); 
                    $bBlur = ($rgbBlur & 0xFF); 
                     
                    // When the masked pixels differ less from the original 
                    // than the threshold specifies, they are set to their original value. 
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)  
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))  
                        : $rOrig; 
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)  
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))  
                        : $gOrig; 
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)  
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))  
                        : $bOrig; 
                     
                     
                                 
                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) { 
                            $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew); 
                            ImageSetPixel($img, $x, $y, $pixCol); 
                        } 
                } 
            } 
        } 
        else{ 
            for ($x = 0; $x < $w; $x++)    { // each row 
                for ($y = 0; $y < $h; $y++)    { // each pixel 
                    $rgbOrig = ImageColorAt($img, $x, $y); 
                    $rOrig = (($rgbOrig >> 16) & 0xFF); 
                    $gOrig = (($rgbOrig >> 8) & 0xFF); 
                    $bOrig = ($rgbOrig & 0xFF); 
                     
                    $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                     
                    $rBlur = (($rgbBlur >> 16) & 0xFF); 
                    $gBlur = (($rgbBlur >> 8) & 0xFF); 
                    $bBlur = ($rgbBlur & 0xFF); 
                     
                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig; 
                        if($rNew>255){$rNew=255;} 
                        elseif($rNew<0){$rNew=0;} 
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig; 
                        if($gNew>255){$gNew=255;} 
                        elseif($gNew<0){$gNew=0;} 
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig; 
                        if($bNew>255){$bNew=255;} 
                        elseif($bNew<0){$bNew=0;} 
                    $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew; 
                        ImageSetPixel($img, $x, $y, $rgbNew); 
                } 
            } 
        } 
        imagedestroy($imgCanvas); 
        imagedestroy($imgBlur); 
         
        return $img; 

    }

 	static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);
        
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        $opacity = 100 - $opacity;
        
        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    } 

    public function imageAlphaBlend($src,$dst){
        $srcR = ($src >> 16) & 0xFF ;
        $srcG = ($src >> 8) & 0xFF ;
        $srcB = $src  & 0xFF ;
        $srcAlpha = ($src >> 24) & 0x7F;
        $srcAlpha = 256 - $srcAlpha;

        if($src == 0x7F000000){
            return $dst;
        }

        if(($src & 0x00FFFFFF) == ($dst & 0x00FFFFFF)){
            //颜色一样
            return (((($srcAlpha * $srcR) >> 8) &  0xFF) << 16) 
                | (((($srcAlpha * $srcG) >> 8) & 0xFF) << 8 ) 
                    | ((($srcAlpha * $srcB) >> 8) & 0xFF);
        }

        $dstR = ($dst >> 16) & 0xFF ;
        $dstG = ($dst >> 8) & 0xFF ;
        $dstB = $dst  & 0xFF ;

        $invAlpha = 256 - $srcAlpha;
        return (((($srcAlpha * $srcR + $invAlpha * $dstR) >> 8) & 0xFF) << 16)
        | (((($srcAlpha * $srcG + $invAlpha * $dstG) >> 8) & 0xFF) << 8)
        | (((($srcAlpha * $srcB + $invAlpha * $dstB) >> 8) & 0xFF) );
    }

    /**
     * 给图片加水印.
     * @imgLogo: 水印图片,Image对象.
     * @x: 水印位置x坐标.
     * @y: 水印位置y坐标.
     * @transparancy: int,透明度,0~100.
     * @logoTransparentColor:logo的透明色,array('r'=>int,'g'=>int,'b'=>int,'a'=>int). 
     */
    public function addWatermark($imgLogo,$x,$y,$transparancy = 0,$logoTransparentColor = NULL) {
        $logoWidth = $imgLogo->getWidth();
        $logoHeight = $imgLogo->getHeight();

        //if(is_array($logoTransparentColor)){
        //    //设置透明色.
        //    $colorTransparent  = imagecolorallocatealpha($imgLogo->resource,
        //        $logoTransparentColor['r'],$logoTransparentColor['g'],
        //        $logoTransparentColor['b'],$logoTransparentColor['a']);
        //    $ret = imagecolortransparent($imgLogo->resource,$colorTransparent);
        //    if($ret !== $colorTransparent){
        //        return FALSE;
        //    }
        //}

        $ret = imagealphablending($this->resource,TRUE);
        imagesavealpha($this->resource,TRUE);
        //$ret = imagealphablending($imgLogo->resource,TRUE);
        //imagesavealpha($imgLogo->resource,TRUE);

        $ret = imagecopy($this->resource,$imgLogo->resource,$x,$y,0,0,
            $logoWidth,$logoHeight);

        return $ret;
    }

    /**
     * @param saturation : [0,10]
     */
    public function changeHSB($hue,$saturation,$brightness){
        $ret = TRUE;
        $H = $this->getHeight();
        $L = $this->getWidth();
        

        for($j=0;$j<$H;$j++){
           for($i=0;$i<$L;$i++){ 
              $rgb = imagecolorat($this->resource, $i, $j); 
           
              $r = ($rgb >> 16) & 0xFF;
              $g = ($rgb >> 8) & 0xFF;
              $b = $rgb & 0xFF;
           
              $getArrayHSB = self::RGBtoHSV($r,$g,$b);
                
              $getArrayHSB[0] = $hue*$getArrayHSB[0];
              $getArrayHSB[0] = max(0.0,min($getArrayHSB[0],360.0)); 
              $getArrayHSB[1] = $saturation*$getArrayHSB[1];
              $getArrayHSB[1] = max(0.0,min($getArrayHSB[1],1.0)); 
              $getArrayHSB[2] = $brightness*$getArrayHSB[2];
              $getArrayHSB[2] = max(0.0,min($getArrayHSB[2],1.0)); 
             
              $getArrayRGB = self::HSVtoRGB($getArrayHSB[0],
                 $getArrayHSB[1],$getArrayHSB[2]);
              if($getArrayRGB[0] != $r || $getArrayRGB[1] != $g || $getArrayRGB[2] != $b){
                  //echo sprintf("original:%d %d %d=> %d %d %d",$r,$g,$b,$getArrayRGB[0],$getArrayRGB[1],$getArrayRGB[2]),PHP_EOL;
                  //$ret =FALSE;
              }
              //$getArrayRGB = array($r,$g,$b);
           
              $hsbrgb = imagecolorallocate($this->resource, 
                 $getArrayRGB[0], $getArrayRGB[1], 
                 $getArrayRGB[2]);
              $result = (($rgb & 0xff000000)|($hsbrgb));
         
              imagesetpixel($this->resource, $i, $j, $result);    
           }
        }   
        return $ret;
    }
 
 

    /**
     * 判断图片是否是gif动画,代码来自
     * http://it.php.net/manual/en/function.imagecreatefromgif.php#59787
     *
     */
    public static function isAnimatedGif($filecontents)
    {
        $str_loc=0;
        $count=0;
        while ($count < 2) # There is no point in continuing after we find a 2nd frame
        {
           $where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
           if ($where1 === FALSE)
           {
                   break;
           }
           else
           {
               $str_loc=$where1+1;
               $where2=strpos($filecontents,"\x00\x2C",$str_loc);
               if ($where2 === FALSE)
               {
                   break;
               }
               else
               {
                   if ($where1+8 == $where2)
                   {
                           $count++;
                   }
                   $str_loc=$where2+1;
               }
           }
        }
    
        if ($count > 1) {
            return TRUE;
    
        }
        else {
            return FALSE;
        }
    }

    static function RGBtoHSV ($r,$g,$b) {
        $r = $r/255.0;
        $g = $g/255.0;
        $b = $b/255.0;

        $min = min( $r, $g, $b );
        $max = max( $r, $g, $b );
        
        $v = $max;                             // v

        $delta = $max - $min;
        if($delta == 0){
            $h = 0;
            $s = 0;
            return array($h,$s,$v);
        }

        if( $max != 0 )
          $s = $delta / $max;           // s
        else {
          // r = g = b = 0            // s = 0, v is undefined
          $s = 0;
          $h = -1;
          return array($h,$s,$v);
        }

        if( $r == $max )
          $h = ( $g - $b ) / $delta;             // between yellow & magenta
        else if( $g == $max )
          $h = 2 + ( $b - $r ) / $delta; // between cyan & yellow
        else
          $h = 4 + ( $r - $g ) / $delta; // between magenta & cyan

        $h *= 60;                             // degrees
        if( $h < 0 )
          $h += 360;
        return array($h,$s,$v);
    }
    static function HSVtoRGB( $h, $s, $v )
    {
    
      if( $s == 0 ) {
        return array($v*255,$v*255,$v*255);
      }
    
      $h /= 60;                      // sector 0 to 5
      $i = floor( $h );
      $f = $h - $i;                    // factorial part of h
      $p = $v * ( 1 - $s );
      $q = $v * ( 1 - $s * $f );
      $t = $v * ( 1 - $s * ( 1 - $f ) );
    
      switch( $i ) {
        case 0:
          $r = $v;
          $g = $t;
          $b = $p;
          break;
        case 1:
          $r = $q;
          $g = $v;
          $b = $p;
          break;
        case 2:
          $r = $p;
          $g = $v;
          $b = $t;
          break;
        case 3:
          $r = $p;
          $g = $q;
          $b = $v;
          break;
        case 4:
          $r = $t;
          $g = $p;
          $b = $v;
          break;
        default:            // case 5:
          $r = $v;
          $g = $p;
          $b = $q;
          break;
      }
      return array($r*255,$g*255,$b*255);
    
    }


    static function RGBtoHSB ($new_r,$new_g,$new_b) {
        //$new_r = $new_r / 255.0;
        //$new_g = $new_g / 255.0;
        //$new_b = $new_b / 255.0;

       $arrayHSB = array($new_r,$new_g,$new_b);
       $h = 0.0 ;
       $minRGB = min($new_r,$new_g,$new_b);
       $maxRGB = max($new_r,$new_g,$new_b);
       
       $delta = ($maxRGB - $minRGB);
       $bright = $maxRGB;
       if ($maxRGB != 0.0) {
           $s = $delta / $maxRGB;
       }
       else {
           $s = 0.0;$h=-1;
       }
       if ($s != 0.0){
          if ($new_r == $maxRGB) {
             $h = ($new_g - $new_b) / $delta;
          }
          else {
             if ($new_g == $maxRGB) {
                $h = 2.0 + ($new_b - $new_r) / $delta;
             }
             else {
                if ($new_b == $maxRGB) {
                   $h = 4.0 + ($new_r - $new_g) / $delta;
                }
             }     
          }
       }  
       else {
          $h = -1.0;
       } 
       $h = $h * 60.0 ;  
       if ($h < 0.0) {$h = $h + 360.0;}
    
       $arrayHSB[0]=$h;
       $arrayHSB[1]=$s;
       $arrayHSB[2]=$bright;
           
       return $arrayHSB;
    }

    static function HSBtoRGB($new_hue,$new_saturation,$new_bright){
       $arrayRGB= array($new_hue,$new_saturation,$new_bright);
       if($new_saturation == 0.0) {
          $r=$new_bright;
          $g=$new_bright;
          $b=$new_bright;
       }   
       
       $new_hue = $new_hue/60.0;      
       $m = floor($new_hue);
       $f = $new_hue - $m;   
       $p = $new_bright * (1.0 - $new_saturation);
       $q = $new_bright * (1.0 - $new_saturation * $f);
       $t = $new_bright * (1.0 - $new_saturation * (1.0 - $f));
       
          
       switch($m) {
          case 0:
             $r = $new_bright;
             $g = $t;
             $b = $p;
             break; 
          case 1:
             $r = $q;
             $g = $new_bright;
             $b = $p;
             break;
          case 2:
             $r = $p;
             $g = $new_bright;
             $b = $t;
             break;
          case 3:
             $r = $p;
             $g = $q;
             $b = $new_bright;
             break;
          case 4:
             $r = $t;
             $r = $p;
             $r = $new_bright;
             break;
          default:      // case 5:
             $r= $new_bright;
             $g = $p;
             $b = $q;
       }
       
       $arrayRGB[0]=$r;
       $arrayRGB[1]=$g;
       $arrayRGB[2]=$b;
       //$arrayRGB[0]=intval($r*255);
       //$arrayRGB[1]=intval($g*255);
       //$arrayRGB[2]=intval($b*255);
       //    
       return $arrayRGB;
    }
}

