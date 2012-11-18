<?php
namespace Snake\Package\User;

class UserExtObject extends \Snake\Package\Base\DomainObject{
	//数据库中的一行纪录
	private $user = array();

    public function __construct($user = array()) {
		$this->user = $user;
	}

    public function getIsTaobaoSeller() {
        return $this->user['is_taobao_seller'];
    }   
    public function getUid() {
        return $this->user['user_id'];
    }   
    public function getUser() {
        return $this->user;
    }   
	public function getAvatarC() {
		return $this->convertAvaterUrl($this->user['avatar_c']);	
	}
//    /**
//     * 转换用户的头像地址为URL
//     * 现在同时支持新旧地址
//     * @param string $picPath
//     * @return string $avatarUrl
//     */
    private function convertAvaterUrl($key) {
		$key = trim($key);
        if(empty($key)){
            return  AVATAR_URL . '/css/images/0.gif';
        }

        if($key[0] == '/'){
            return AVATAR_URL . $key;
        }
        return  AVATAR_URL . '/' . $key;
   }

}
