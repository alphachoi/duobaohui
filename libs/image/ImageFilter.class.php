<?php
namespace Snake\Libs\Image;

Use \Snake\Libs\Image\Image;
/**
 * 图片滤镜功能
 * @author wangxi
 *
 */
class ImageFilter {

    private $ndw;
    private $npw;
    private $nmw;
    private $tmpNmw;
    private $openStatus;
    var $lastError; //array('line'=>__LINE__,'func'=>__FUNCTION__,'msg'=>str)

    function __construct( $img) {

        $this->tmpNmw = NewMagickWand ();
        $this->ndw = NewDrawingWand ();
        $this->nmw = NewMagickWand ();
        $this->npw = NewPixelWand ( "black" );
        if($img instanceof Image){
            $imgBytes = $img->getContent();
            $this->openStatus = MagickReadImageBlob ( $this->nmw, $imgBytes );
        }

    }
    function __destruct() {
        //注销资源 fix hefe and hsat can't convert problem
        /*DestroyDrawingWand ( $this->ndw );
        DestroyPixelWand ( $this->npw );
        DestroyMagickWand ( $this->nmw );
        DestroyMagickWand ( $this->tmpNmw );*/

    }

    function filterTuning() {
       //$openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_LOMO);
        if ($_GET['png']) {
            $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_TUNING_PNG);
        }
        else {
            $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_TUNING);
        }
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        MagickGammaImage($this->nmw, $_GET['red'], MW_RedChannel);
        MagickGammaImage($this->nmw, $_GET['green'], MW_GreenChannel);
        MagickGammaImage($this->nmw, $_GET['blue'], MW_BlueChannel);
        MagickModulateImage ($this->nmw, 100, $_GET['bhd'], 100);
        $width = MagickGetImageWidth ( $this->nmw);
        $height = MagickGetImageHeight ( $this->nmw);
        if ($_GET['cover'] != 1) {
            $this->tmpNmw = $this->nmw;
        }
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 );
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_DarkenCompositeOp, 00, 0 );
        return TRUE;
    }

    function filterLomo() {
        //$openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_LOMO);
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_LOMO);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        //$openStatus = MagickReadImage ( $this->nmw, $this->imageSrcFile );
        MagickGammaImage($this->nmw, 1, MW_RedChannel);
        MagickGammaImage($this->nmw, 1.7, MW_GreenChannel);
        MagickGammaImage($this->nmw, 1.3, MW_BlueChannel);
        MagickModulateImage ($this->nmw, 100, 130, 100);
        $width = MagickGetImageWidth ($this->nmw);
        $height = MagickGetImageHeight ($this->nmw);
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0);
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_ColorBurnCompositeOp, 0, 0 );
        return TRUE;
    }

    function filterVintage() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_VINTAGE);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        MagickGammaImage($this->nmw, 2, MW_RedChannel);
        MagickGammaImage($this->nmw, 1.2, MW_GreenChannel);
        MagickGammaImage($this->nmw, 0.8, MW_BlueChannel);
        MagickModulateImage ($this->nmw, 100, 60, 100);
        $width = MagickGetImageWidth ( $this->nmw);
        $height = MagickGetImageHeight ( $this->nmw);
        //$this->tmpNmw = $this->nmw;
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0);
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_SoftLightCompositeOp, 0, 0 );
        return TRUE;
    }

    function filterWarm() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_WARM);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        MagickGammaImage($this->nmw, 2, MW_RedChannel);
        MagickGammaImage($this->nmw, 1.5, MW_GreenChannel);
        MagickGammaImage($this->nmw, 1.5, MW_BlueChannel);
        MagickModulateImage ( $this->nmw, 100, 60, 100);
        $width = MagickGetImageWidth ( $this->nmw );
        $height = MagickGetImageHeight ( $this->nmw );
        //$this->tmpNmw = $this->nmw;
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 );
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_LightenCompositeOp, 0, 0 );
        return TRUE;
    }

    function filterHefe() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_WARM);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        MagickGammaImage($this->nmw, 2.3, MW_RedChannel);
        MagickGammaImage($this->nmw, 0.8, MW_GreenChannel);
        MagickGammaImage($this->nmw, 0.8, MW_BlueChannel);
        MagickModulateImage ( $this->nmw, 100, 50, 100);
        $width = MagickGetImageWidth ( $this->nmw);
        $height = MagickGetImageHeight ( $this->nmw);
        $this->tmpNmw = $this->nmw;
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 );
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_DarkenCompositeOp, 0, 0 );
        return TRUE;
    }

    function filterHsat() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_WARM);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        MagickGammaImage($this->nmw, 1.5, MW_RedChannel);
        MagickGammaImage($this->nmw, 1.5, MW_GreenChannel);
        MagickGammaImage($this->nmw, 1.5, MW_BlueChannel);
        MagickModulateImage ( $this->nmw, 100, 200, 100);
        $width = MagickGetImageWidth ( $this->nmw);
        $height = MagickGetImageHeight ( $this->nmw);
        $this->tmpNmw = $this->nmw;
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 );
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_DarkenCompositeOp, 0, 0 );
        return TRUE;
    }

    function filterFilm() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_FILM);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,'read_img');
            return FALSE;
        }
        $ret = MagickGammaImage($this->nmw, 1.5, MW_RedChannel);
        if(!$ret){
            $this->setError(__LINE__,__FUNCTION__,'gamma');
            return FALSE;
        }
        $ret = MagickGammaImage($this->nmw, 1.5, MW_GreenChannel);
        if(!$ret){
            $this->setError(__LINE__,__FUNCTION__,'gamma');
            return FALSE;
        }
        $ret = MagickGammaImage($this->nmw, 0.7, MW_BlueChannel);
        if(!$ret){
            $this->setError(__LINE__,__FUNCTION__,'gamma');
            return FALSE;
        }

        $ret = MagickModulateImage ( $this->nmw, 100, 60, 100);
        if(!$ret){
            $this->setError(__LINE__,__FUNCTION__,'modulate');
            return FALSE;
        }

        $width = MagickGetImageWidth ( $this->nmw);
        $height = MagickGetImageHeight ( $this->nmw);
        //$this->tmpNmw = $this->nmw;
        if(!MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 )){
            $this->setError(__LINE__,__FUNCTION__,'modulate');
            return FALSE;
        }
        if(!MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_DarkenCompositeOp, 0, 0 )){
            $this->setError(__LINE__,__FUNCTION__,'modulate');
            return FALSE;
        }
        return TRUE;
        
    }

    function filterBw() {
        $openStatus = MagickReadImage ( $this->tmpNmw, IMAGE_FILTER_MASK_LIGHT);
        if(!$openStatus){
            $this->setError(__LINE__,__FUNCTION__,
                sprintf('read_img:%s',IMAGE_FILTER_MASK_LIGHT));
            return FALSE;
        }
        MagickGammaImage($this->nmw, 1.8, MW_RedChannel);
        MagickGammaImage($this->nmw, 1.8, MW_GreenChannel);
        MagickGammaImage($this->nmw, 1.8, MW_BlueChannel);
        MagickModulateImage ( $this->nmw, 100, 0, 100);
        $width = MagickGetImageWidth ( $this->nmw );
        $height = MagickGetImageHeight ( $this->nmw );
        $this->tmpNmw = $this->nmw;
        MagickResizeImage ( $this->tmpNmw, $width, $height, MW_LanczosFilter, 1.0 );
        MagickCompositeImage ( $this->tmpNmw, $this->nmw, MW_MultiplyCompositeOp, 0, 0 );
        return TRUE;
    }
    function getBytes() {
        return MagickGetImageBlob($this->tmpNmw);
    }

//    function imageConverter(  ) {
//        //MagickWriteImages($this->tmpNmw, $this->imageSrcFile ,TRUE);
//        $bytes = MagickGetImageBlob($this->tmpNmw);
//        //echo $this->imageSrcFile . ',len = ' . strlen($bytes);
//
//        return $this->imgStg->updateImage($this->imageSrcFile,$bytes);
//    }

    function show( ) {
        MagickEchoImageBlob($this->tmpNmw);
        //$bytes = MagickGetImageBlob($this->tmpNmw);
        //echo strlen($bytes);
    }

    function setError($line,$func,$str){
        $this->lastError  = array('file' => __FILE__,
                            'line' => $line,
                            'func' => $func,
                            'msg'  => $str);
        
    }

}

