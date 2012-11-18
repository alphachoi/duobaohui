<?php

namespace Snake\Modules\User;

Use Snake\Package\User\User;

class Aliyun_callback extends \Snake\Libs\Controller {

    public function run() {

        $this->head = 200;
        //以下是密匙数据 我们平台会自动生成唯一的分配给你们的站的 调试的时候双方暂时简单约定
        $siteownerid = '8d76cbee8ef80379c2340261bcc69d9c'; //密匙二 加密验证用 
        $sitehash = '10UwYHAwMIAg8LDAkBV1ZSVQFSClRTXgJVXAQAUQdRAgE'; //密匙三 唯一密匙 
        //换成你们当前登录用户的UID （需要你们程序处理取得）
        $uid = $this->userSession['user_id'];;

        $request = array_merge($_GET, $_POST);
        //去掉ajax读取时自动加上的callback参数--为了验证能通过
        unset($request['callback']);
        unset($request['_']);

        $request = $this->strips($request);

        //首先进行密匙验证
        ksort($request);
        reset($request);
        $arg = '';
        foreach ($request as $key => $value) {
            if ($value && $key != 'sig') {
                $arg .= "$key=$value&";
            }
        }

        if (empty($siteownerid) || md5($arg . $siteownerid) != $request['sig']) {
            $results = 'check_user(' . json_encode('Error Sign') . ')';
            echo $results;
            exit;
        }

        //密匙验证通过 然后进行时间有效性验证 暂定30分钟
        $t = $request['t'];
        if (time() - $t > 7200) {
            $results = 'check_user(' . json_encode('Time Out , Error Sign') . ')';
            echo $results;
            exit;
        }

        //分支功能选择处理 （目前接口只有2个分支功能 简单处理即可）
        $gotype = $request['gotype'];
        if ($gotype) {
            $backurl = $request['back_url'];
            $this->goUrl($gotype, $backurl);
        } else {
            $this->get_login_user($uid, $sitehash, $siteownerid);
        }
    }

    /*
     * 函数一：获取当前登录用户的信息 并组装成JSONP各式输出
     * $uid:当前登录用户UID
     * $sitehash ,$siteownerid:约定的密匙
     */

    private function get_login_user($uid, $sitehash, $siteownerid) {
        if (!$uid) {
            $results = 'check_user(' . json_encode(false) . ')';
            echo $results;
            exit;
        }
        
        $user = new User();
        $userInfo = $user->getUserInfo(765, array('nickname', 'avatar_b', 'ctime'));
        //需要的数据 sitehash 和 user_id 和 user_name是必须有的 其他可选  有汉字必须转码为utf-8
        $users = array(
            'sitehash' => $sitehash, //唯一密匙*
            'icon' => $userInfo['avatar_b'], //用户头像
            'user_id' => $uid, //用户论坛id *
            'reg_date' => $rt['ctime'], //用户注册时间 *
            'user_name' => $rt['nickname'], //用户论坛用户名 * 如果网站是非utf-8编码的则需要转码成UTF-8 
        );
        $users['sig'] = create_sign($users, $siteownerid);
        $results = 'check_user(' . json_encode($users) . ')';
        echo $results;
    }

    //签名认证函数 你们返回给我们的数据也要验证
    private function create_sign($params, $code) {
        ksort($params);
        reset($params);
        $str = '';
        foreach ($params as $k => $v) {
            $str .="$k=$v&";
        }
        $sig = md5($str . $code);
        return $sig;
    }

    /*
     * 函数二：根据类型不同 跳转到不同地方去
     * $gotype :类型（login reg logout）
     * backurl :会跳URL （我们参数上会带上 比如在淘满意点登录 跳到你的论坛 登录成功后 会跳回套满意平台） 注意jumpurl
     * 要换成你们登录的回跳参数 如果论坛没会跳功能 那不可用了
     */

    private function goUrl($gotype, $backurl) {
        //这里填你们自己论坛的 登录 注册 退出 的URL地址 jumpurl是回跳地址 放你们自己定义的回跳参数
        $login_url = "http://www.meilishuo.com/logon?";
        $reg_url = "http://www.meilishuo.com/users/register/?frm=_tuanaliyun";
        $logout_url = "http://www.meilishuo.com/users/logout?";

        if ($gotype == "login") {
            $site_url = $login_url;
        } elseif ($gotype == "reg") {
            $site_url = $reg_url;
        } else {
            $site_url = $logout_url;
        }
        $url = $site_url . '&jumpurl=' . urlencode($backurl);
        header('Location: ' . $url);
    }

    //过滤函数
    private function strips($param) {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                $param[$key] = $this->strips($value);
            }
        } else {
            $param = stripslashes($param);
        }
        return $param;
    }

}

?>
