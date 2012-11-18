<?php
namespace Snake\Libs\Phpmailer;

class SendMail{
	public static function run($sendto_email, $subject, $body, $user_name=''){
			$mail = new Phpmailer();

			$mail->IsSMTP(); // send via SMTP	//$mail->IsMail(); // send via mail

			$mail->Host = "smtp.exmail.qq.com"; // SMTP servers 注意：好像听说是只有2006年以前申请的163邮箱具有此功能
			$mail->SMTPAuth = true; // turn on SMTP authentication
			$mail->Username = "noreply@mamamiya.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名
			$mail->Password = "mmmy-147963"; // SMTP password(我把我的密码给隐了)
			$mail->From = "noreply@mamamiya.cn"; // 发件人邮箱
			$mail->FromName = "duobaohui"; // 发件人

			$mail->CharSet = "utf-8"; // 这里指定字符集！
			$mail->Encoding = "base64";
			$mail->AddAddress($sendto_email, $user_name); // 收件人邮箱和姓名
			$mail->IsHTML(true); // send as HTML
			$mail->Subject = $subject; // 邮件主题
			$mail->Body = $body; // 邮件内容
			$mail->AltBody = "text/html";
			if ($mail->Send()) {
				return true;
			}

			return false;
	}
}
