<?php
namespace Snake\Libs\Base;

/**
 * 验证码
 *
 * author: chaoguo@meilishuo.com
 */
class Captcha {
	//验证码位数
	private $captchaCodeNum = 4;

	//产生的验证码
	private $captchaCode = '';

	//验证码的图片
	private $captchaImage = '';

	//干扰像素
	private $captchaDisturbColor = '';

	//验证码的图片宽度
	private $captchaImageWidth = 80;

	//验证码图片高度
	private $captchaImageHeight = 20;

	//难易程度(1~5)
	private $level = 3;

	//验证码字体大小
	private $size = 20;

	//字体
	private $font = 'angelina.ttf';
	private $allowFont = array(
		'angelina.ttf',

	);	

	//验证码底图
	private $background = 'captchaBackground.jpg';
	private $allowBackground = array(
		'captchaBackground.jpg',

	);

	/**
	 * constructer
	 * 
	 */
	public function __construct() {

	}

	/**
	 * 获取验证码
	 *
	 */
	public function GetCaptchaCode() {
		return $this->captchaCode;
	}

	/**
	 * 设置验证码
	 *
	 */
	public function SetCaptchaCode() {
		$this->captchaCode = strtoupper(substr(preg_replace('/0|o|1|i|9|g/i', '', md5(rand())), 0, $this->captchaCodeNum));
	}

	/**
	 * 验证码难易度
	 */
	public function SetCaptchaLevel($level) {
		if ($level >= 1 && $level <= 5) {
			$this->level = (int) $level;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 验证码底图
	 */
	public function setCaptchaBackground($background) {
		if (in_array($background, $this->allowBackground)) {
			$this->background = ROOT_PATH . '/libs/base/img/' . $background;
		}
		else {
			$this->background = ROOT_PATH . '/libs/base/img/' . $this->background;
		}
	}

	public function setCaptchaFont($font) {
		if (in_array($font, $this->allowFont)) {
			$this->font = ROOT_PATH . '/libs/base/img/' . $font; 
		}	
		else {
			$this->font = ROOT_PATH . '/libs/base/img/' . $this->font; 
		}
	}

	/**
	 * 设置验证码宽度和高度
	 * @param $width integer 
	 * @param $height integer
	 */
	public function SetCaptchaImageWidthAndHeight($width, $height) {
		if (empty($width) || empty($height)) {
			return FALSE;
		}
		$this->captchaImageWidth = $width;
		$this->captchaImageHeight = $height;
	}

	/**
	 * 生成验证图像
	 *
	 */
	private function CreateImage() {
		$srcImage = imagecreatefromjpeg($this->background);
		$this->captchaImage = imagecreatetruecolor($this->captchaImageWidth, $this->captchaImageHeight);
		imagecopyresized($this->captchaImage, $srcImage, 0, 0, 0, 0, $this->captchaImageWidth, $this->captchaImageHeight, 
			$this->captchaImageWidth, $this->captchaImageHeight);
		return $this->captchaImage;
	}

	/**
	 * 干扰线,根据难易程度画点，画线
	 */
	private function SetDisturbColor() {
		$this->captchaDisturbColor = imagecolorallocate($this->captchaImage, 102, 102, 102);
		$pixelBase = 50 * $this->level;
		$lineBase = $this->level;
		for ($i = 0; $i <= $pixelBase; $i++) {
			imagesetpixel($this->captchaImage, rand(2, $this->captchaImageWidth), rand(2, $this->captchaImageHeight), $this->captchaDisturbColor);
		}
		for ($i = 0; $i <= $lineBase; $i++) {
			imageline($this->captchaImage, rand(2, $this->captchaImageWidth), rand(2, $this->captchaImageHeight), rand(2, $this->captchaWidth), rand(2, $this->captchaHeight), $this->captchaDisturbColor);
		}
		for ($i = 0; $i < 2; $i++) {
			$this->ImageBoldLine($this->captchaImage, rand(2, $this->captchaImageWidth), rand(2, $this->captchaHeight), rand(2, $this->captchaImageWidth), rand(2, $this->captchaImageHeight), $this->captchaDisturbColor);
		}
	}

	public function ImageBoldLine($resource, $x1, $y1, $x2, $y2, $color, $boldNess = 2, $func = 'imageline') {
		$center = round($boldNess / 2);
		for ($i = 0; $i < $boldNess; $i++) {
			$a = $center - $i;
			if ($a < 0) {
				$a -= $a;
			}
			for ($j = 0; $j < $boldNess; $j++) {
				$b = $center - $j;
				if ($b < 0) {
					$b -= $b;
				}
				$c = sqrt($a * $a + $b * $b);
				if ($c <= $boldNess) {
					$func($resource, $x1 + $i, $y + $j, $x2 + $i, $y2 + $j, $color);
				}
			}
		}
	}


	/**
	 * 将验证码写到图像
	 */
	private function WriteCaptchaCodeToImage() {
		for ($i = 0; $i <= $this->captchaCodeNum; $i++) {
			$bgColor = imagecolorallocate($this->captchaImage, 0, 0, 0);
			$x = floor($this->captchaImageWidth / $this->captchaCodeNum) * $i;
			$y =  $this->captchaImageHeight - ($this->captchaImageHeight - $this->size) / 2;	
			switch ($this->level) {
				case 1:
					$end = 10;
					break;
				case 2:
					$end = 30;
					break;	
				case 3:
					$end = 75;
					break;
				case 4:
					$end = 90;
					break;
				case 5:
					$end = 120;
					break;
				default:
					break;
			}
			imagettftext($this->captchaImage, $this->size, rand(0, $end), $x + 15, $y, $bgColor, $this->font, $this->captchaCode[$i]);
		}
	}

	public function OutCheckImage() {
		$this->CreateImage();
		$this->SetDisturbColor();
		$this->WriteCaptchaCodeToImage();
		return $this->captchaImage;
	}
}
