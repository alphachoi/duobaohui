<?php
namespace Snake\Package\User;

/**
 * 格式验证
 *
 */
USE \Snake\libs\Cache\Memcache AS Memcache;

class UserFormat {
	
	const EMAIL_EXP = "/^[0-9a-zA-Z]+([_a-z0-9\-\.]+)*@[a-zA-Z0-9]{2,}(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/";
	const NICKNAME_EXP = "/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_]+$/u";
	const URL_EXP = "|^http://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?$|";
	
	private $rule = 'DFA_table';
	private $allowRules = array('DFA_table', 'DFA_register');

	public function __construct() {

	}

	public function setMaskRule($rule) {
		if (!empty($rule) && in_array($rule, $this->allowRules)) {
			$this->rule = $rule;	
		}	
		return TRUE;
	}

	/**
	 * 邮箱验证
	 * @param $email string 
	 *
	 */
	public function emailFormat($email) {
		if (empty($email)) {
			return FALSE;
		}
		if (preg_match(self::EMAIL_EXP, $email)) {
			$dnsV = explode("@", $email);
			$dns = array_pop($dnsV);
		    $cacheHelper = Memcache::instance();
			$cacheKey = 'Register:Emaildns';
			$dnsS = $cacheHelper->get($cacheKey);
			$dnsArray = array();
			!empty($dnsS) && $dnsArray = explode(",", $dnsS);
			if (!in_array($dns, $dnsArray)) {
				if (checkdnsrr($dns, "MX") == FALSE) {
					return FALSE;
				}
				$dnsArray[] = $dns;
				$dnsString = implode(',', $dnsArray);
				!empty($dnsString) && $cacheHelper->set($cacheKey, $dnsString, time() + 36000);
            }
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 呢称验证
	 * @param $nickname string
	 */
	public function nicknameFormat($nickname) {
		$length = (strlen($nickname) + mb_strlen($nickname, 'utf-8' )) / 2;
		if ($length < 1 || $length > 20 || !preg_match(self::NICKNAME_EXP, $nickname)) {
			return FALSE;
		}
		return TRUE;
	}

	public function urlFormat($url) {
		if (empty($url)) {
			return FALSE;
		}
		if (!preg_match(self::URL_EXP, $url)) {
			return FALSE;	
		}
		return TRUE;
	}

	public function maskwordFormat($word) {
		if (empty($word)) {
			return FALSE;
		}	
	    $maskWords = new \Snake\Package\Spam\MaskWords($word, $this->rule);
        $mask = $maskWords->getMaskWords();	
		if (!empty($mask['maskWords'])) {
			return FALSE;
		}
		return TRUE;
	}
}
