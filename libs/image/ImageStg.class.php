<?php
namespace Snake\Libs\Image;

Use \Snake\Libs\Image\ImageLib;
Use \Snake\Libs\Image\Image;

class ImageStg {
    static $imgKinds;
    public function __construct() {
        self::$imgKinds = array('pic','ap','img','glogo','tmp');
    }
    /**
     * 分析id中包含的信息.
     * id的形式{kind}/{thumb_type}/{middle}/
     *     {base}_{width}_{height}[_{filter}].{ext}.
     *  还有一些组合信息.
     *  {file_name} = {base}_{width}_{height}.{ext}
     *  {orig_id} = {kind}/_o/{middle}/{base}_{width}_{height}.{ext}
     *  {last_id} 为最近一次处理前的id，比如如果有filter，则去掉filter。
     *            如果有thumb，则将thumb换成_o.
     *            目前只支持两次操作，即先thumb,再filter.
     *
     * @param id: 源图id.
     * @return : 成功返回数组,array(
     *          'width'=>$width,
     *           'height'=>$height,
     *           'ext'=>$ext,
     *           'middle'=>路径的中间部分.
     *           'thumb_type'=>$thumbType,
     *           'kind' => $kind,
     *           'filter' => $filter
     *           'file_name' => ,
     *           'orig_id' =>,
     *           'last_id' =>)
     */
    static public function parseImgId($id){
        $id = trim($id);
        if(!is_string($id) || empty($id)){
            return FALSE;
        }
        $pos = strrpos($id,'.');
        if($pos === FALSE){
            return FALSE;
        }
        $result['ext'] = substr($id,$pos + 1);
        if(!ImageLib::isValidImageExt($result['ext'])){
            return FALSE;
        }
        $pos1 = strpos($id,'/');
        if($pos1 === FALSE){
            return FALSE;
        }
        $result['kind'] = substr($id,0,$pos1);
        $pos2 = strpos($id,'/',$pos1 + 1);
        if($pos2 === FALSE){
            return FALSE;
        }
        $result['thumb_type'] = substr($id,$pos1 + 1, $pos2 - $pos1 - 1);

        $pos3 = strrpos($id,'/');
        if($pos3 < $pos2){
            return FALSE;
        }
        $result['middle'] = substr($id,$pos2 + 1, $pos3 - $pos2 - 1);

        $fileBase = substr($id,$pos3 + 1, $pos - $pos3 - 1);
        $result['file_name'] = substr($id,$pos3 + 1);
        $parts = explode('_',$fileBase);
        if(count($parts) < 3 || count($parts) > 5) {
            return FALSE;
        }
        $result['base'] = $parts[0];
        $result['width'] = intval($parts[1],10);
        $result['height'] = intval($parts[2],10);
        $result['filter'] = '';
        if(count($parts) == 4){
            if(ctype_digit($parts[3])){
                $result['version'] = intval($parts[3]);
            }
            else {
                $result['filter'] = $parts[3];
            }
        }
        else if(count($parts) == 5){
            $result['filter'] = $parts[3];
            $result['version'] = intval($parts[4]);
        }
       $result['orig_id'] = sprintf('%s/%s/%s/%s_%d_%d.%s',
            $result['kind'],THUMB_TYPE_ORIG,$result['middle'],$result['base'],
            $result['width'], $result['height'],$result['ext']);
        return $result;
    }
    static function calcPictureThumbInfo($oWidth,$oHeight,$tWidth,$tHeight,$method){
        if($oWidth == 0 || $oHeight == 0 || $tWidth == 0 || $tHeight == 0){
            return FALSE;
        }
        $tRatio = $tWidth / $tHeight;
        $oRatio = $oWidth / $oHeight;
        if (THUMB_SCALE_ONE == $method) {
            $method = $tRatio > $oRatio ? THUMB_SCALE_WIDTH : THUMB_SCALE_HEIGHT;
        }
        switch ($method) {
        case THUMB_SCALE_BOTH:
            return Image::calcScaleImgSize($oWidth,$oHeight,$tWidth,$tHeight);
        case THUMB_SCALE_WIDTH:
            $sizeInfo = Image::calcScaleImgSize($oWidth,$oHeight,$tWidth);
            $currentWidth = $oWidth <= $tWidth ? $oWidth : $tWidth;
            $currentHeight = $oHeight * $currentWidth / $oWidth;
            // if current size is smaller, do not crop more
            $currentWidth < $tWidth && $tWidth = $currentWidth;
            $currentHeight < $tHeight && $tHeight = $currentHeight;
            if ($tWidth != $currentWidth || !$tHeight != $currentHeight) {
                $sizeInfo['width'] = (int)$tWidth;
                $sizeInfo['height'] = (int)$tHeight;
            }
            return $sizeInfo;

        case THUMB_SCALE_HEIGHT:
            $sizeInfo = Image::calcScaleImgSize(NULL, $tHeight);
            // We crop the image anyway.
            $currentHeight = $oHeight <= $tHeight ? $oHeight : $tHeight;
            $currentWidth = $oWidth * $currentHeight / $oHeight;
            // if current size is smaller, do not crop more
            $currentWidth < $tWidth && $tWidth = $currentWidth;
            $currentHeight < $tHeight && $tHeight = $currentHeight;
            if ($tWidth != $currentWidth || $tHeight != $currentHeight) {
                $sizeInfo['width'] = (int)$tWidth;
                $sizeInfo['height'] = (int)$tHeight;
            }
            return $sizeInfo;
            break;
        default:
            // should never come in here!
            break;
        }
        return FALSE;
    }
    static public function getThumbId($origId,$thumbType) {
        if(empty($origId) || empty($thumbType)){
            return '';
        }
        $thumbId = str_replace('/_o/','/' . $thumbType . '/',$origId);
        return $thumbId;
    }
    /**
     * 根据图片id获取图片内容.
     * @param id: 图片path,是t_dolphin_twitter_picture
     * 或者user_profile_ext中存储的图片路径.
     * 实际是key-value存储的key,或者是文件系统的路径.
     * @return 成功返回图片代表图片内容的string，失败返回FALSE.
     */
    public function getImage($id) {
        ImageLib::ImageLog('LOG',"act=getImage id=".$id);
        $bytes = FALSE;
        $id = trim($id);
        if(!is_string($id) || empty($id)){
            return FALSE;
        }
        //$num = rand() % count($GLOBALS['IMAGE_SERVICE']['SERVERS']);
        //$domain = $GLOBALS['IMAGE_SERVICE']['SERVERS'][$num];
        //$url = $domain . $GLOBALS['IMAGE_SERVICE']['PIC_GET'] . $id; 
        $uri = $GLOBALS['IMAGE_SERVICE']['PIC_GET'] . $id;
        $url = ImageLib::ComposeImgServiceReqUrl($uri, IMAGE_SERVICE_GET_IMAGE);
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_TIMEOUT,3);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if($code != 200){
            ImageLib::ImageLog('ERROR',sprintf('pos=%s:%d act=getImage'
                . '  id=%s code=%d',__FILE__,
                __LINE__,$id, $code));
            return FALSE;
        }
        ImageLib::ImageLog('DEBUG',sprintf('pos=%s:%d act=getImage'
            . '  id=%s code=%d url=%s',__FILE__,
            __LINE__,$id, $code,$url));
        return $body;
    }

    static public function genFilterImgId($id,$filterType){
        if(!is_string($filterType) || empty($filterType)){
            return FALSE;
        }
        $pos = strrpos($id,'.');

        if($pos === FALSE){
            return FALSE;
        }
        return substr($id,0,$pos) . '_' . $filterType  . substr($id,$pos);
    }
    /**
     *  保存源图接口.
     *  @param bytes: 图的内容，是个二进制数组
     *  @ext : 扩展名,如.jpg,.png等.
     *  @kind : 图片种类,avatar,pic.
     *  @return : 源图的id,thunb的id可以简单通过加上前缀{缩略图类型}而得到
     *         比如源图为sxxx.jpg,缩略图为T1sxxx.jpg.
     *         失败返回FALSE
     */
    public function saveOrigImage($bytes,$ext,$kind) {
        if(!ImageLib::isValidImageExt($ext)){
            ImageLib::ImageLog('ERROR',sprintf('act=save_img err=invalid_ext ext=%s'
                .' kind=%s', $ext,$kind));
            return FALSE;
        }
        //图片类别不支持.
        if(!self::isValidImgKind($kind)){
            ImageLib::ImageLog('ERROR',sprintf('act=save_img err=invalid_kind ext=%s'
                .' kind=%s', $ext,$kind));
            return FALSE;
        }
        $info = $this->postImage($bytes, $ext, $kind);
        if ($info == FALSE) {
            ImageLib::ImageLog('ERROR',sprintf('act=save_img err=postImage failed'));
            return FALSE;
        }
        if($info['ret'] == 0){
            ImageLib::ImageLog('INFO',sprintf('act=save_orig ext=%s'
                .' kind=%s id=%s ret=%s', $ext,$kind,$info['data']['n_pic_file'],strval($info['ret'])));
            return $info['data']['n_pic_file'];
        }
        ImageLib::ImageLog('ERROR',sprintf('act=save_img err=save ext=%s' . ' kind=%s', $ext,$kind));
        return FALSE;
    }
    static public function isValidImgKind($kind){
        if(in_array($kind,self::$imgKinds)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 生成源图的唯一id,这个id目前是兼容文件系统路径的.
     * @return string
     */
    static public function genOrigImgId($bytes,$ext,$width,$height,$kind) {
        $md5 = md5($bytes);
        $level1Dir = substr($md5,0,2);
        $level2Dir = substr($md5,2,2);
        $base = substr($md5,4);
        return sprintf('%s/%s/%s/%s/%s_%d_%d.%s',$kind,THUMB_TYPE_ORIG,$level1Dir,
                       $level2Dir,$base, $width, $height,$ext);
    }
    /**
     * 使用图片上传接口，将图片存储 
     * @return array 返回成功时举例如下
     * array(3) {
     *   ["ret"]=>  string(1) "0"
     *   ["msg"]=>  string(7) "success"
     *   ["data"]=> array(5) {
     *                 ["nwidth"]=>int(200)
     *                 ["nheight"]=>int(800)
     *                 ["n_pic_file"]=>string(53) "pic/_o/e0/32/1109d19f3efa7a9ce95643fd1fa8_200_800.jpg"
     *                 ["pic_id"]=>string(7) "2343973"
     *                 ["size"]=>int(67343)
     *               }
     *  }
     */
    public function postImage($bytes, $ext, $kind) {
        //$name = self::genOrigImgId($bytes,$ext,$width,$height,$kind); 
        $name = md5($bytes);
        //$num = rand() % count($GLOBALS['IMAGE_SERVICE']['SERVERS']);
        //$domain = $GLOBALS['IMAGE_SERVICE']['SERVERS'][$num];
        //$url = $domain . $GLOBALS['IMAGE_SERVICE']['PIC_UPLOAD']; 
        $uri = $GLOBALS['IMAGE_SERVICE']['PIC_UPLOAD']; 
        $url = ImageLib::ComposeImgServiceReqUrl($uri, IMAGE_SERVICE_SET_IMAGE);
        ImageLib::ImageLog('LOG',sprintf('act=postImage url=%s',$url));

        $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = $MPboundary . "\r\n";
        $multipartbody .= 'Content-Disposition: form-data; filename='.$name. "\r\n";
        switch($ext) {
        case "png":
            $multipartbody .= 'Content-Type: image/png'. "\r\n\r\n";
            break;
        case "jpg":
            $multipartbody .= 'Content-Type: image/jpg'. "\r\n\r\n";
            break;
        case "gif":
            $multipartbody .= 'Content-Type: image/gif'. "\r\n\r\n";
            break;
        case "jpeg":
            $multipartbody .= 'Content-Type: image/jpeg'. "\r\n\r\n";
            break;
        default:
            $multipartbody .= 'Content-Type: image/jpg'. "\r\n\r\n";
            break;
        }    
        $multipartbody .= $bytes."\r\n";

        $key = "kind";
        $value = $kind;
        $multipartbody .= $MPboundary . "\r\n";
        $multipartbody .= 'content-disposition: form-data;name="'.$key."\r\n\r\n";
        $multipartbody .= $value . "\r\n";

        $multipartbody .= "\r\n". $endMPboundary;
        $ch = curl_init(); 
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt( $ch , CURLOPT_POST, 1 );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $multipartbody );
        $header_array = array("Content-Type: multipart/form-data; boundary=$boundary" , "Expect: ");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array );  
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER , true);
        curl_setopt($ch, CURLINFO_HEADER_OUT , true);
        $info = curl_exec($ch);
        $headersize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_errno > 0) {
            ImageLib::ImageLog('ERROR',sprintf('act=postImage curl post data failed,curl_errno=%s,curl_error=%s', $curl_errno, $curl_error));
            return FALSE;
        }
        if ($code != 200) {
            ImageLib::ImageLog('ERROR', sprintf('act=postImage return code is %s', $code)); 
            return FALSE;
        }
        if (!empty($info)) {
            $info = substr($info ,$headersize);
            $info = json_decode($info,true);
            ImageLib::ImageLog('LOG', sprintf('act=postImage ret=%s,msg=%s', $info['ret'], $info['msg']));
            return $info;
        } else {
            return FALSE;
        }
    }
}




