<?php
namespace Snake\Package\Oauth\Taobao_sdk\Top\Request;

/**
 * TOP API: taobao.user.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:25.0
 */
class UserBuyerGetRequest
{
	/** 
	 * 需返回的字段列表。可选值：User结构体中的所有字段；以半角逗号(,)分隔。不支持：地址，真实姓名，身份证，手机，电话
	 **/
	private $fields;
	
	/** 
	 * 用户昵称，如果昵称为中文，请使用UTF-8字符集对昵称进行URL编码。
<br><font color="red">注：在传入session的情况下,可以不传nick，表示取当前用户信息；否则nick必须传.<br>
自用型应用不需要传入nick
</font>
	 **/
	private $nick;
	
	private $apiParas = array();
	
	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setNick($nick)
	{
		$this->nick = $nick;
		$this->apiParas["nick"] = $nick;
	}

	public function getNick()
	{
		return $this->nick;
	}

	public function getApiMethodName()
	{
		return "taobao.user.buyer.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
