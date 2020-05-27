<?php
namespace Wnd\Action;

use Exception;
use Wnd\Model\Wnd_Auth;

/**
 *@since 2019.01.28 ajax 发送手机或邮箱验证码
 *@param $_POST['type']							验证类型
 *@param $_POST['is_email']						发送类型（邮件，短信等）
 *@param $_POST['template']						信息模板
 *@param $_POST['phone'] or $_POST['email']		手机或邮件
 */
class Wnd_Send_Code extends Wnd_Action_Ajax {

	public static function execute(): array{
		$type           = $_POST['type'] ?? '';
		$is_email       = $_POST['is_email'] ?: false;
		$text           = $is_email ? __('邮箱', 'wnd') : __('手机', 'wnd');
		$template       = $_POST['template'] ?: wnd_get_config('sms_template_v');
		$email_or_phone = $_POST['email'] ?? $_POST['phone'] ?? null;
		$current_user   = wp_get_current_user();

		// 防止前端篡改表单：校验验证码类型及接受设备
		if (!wp_verify_nonce($_POST['type_nonce'], $is_email ? 'email' . $type : 'sms' . $type)) {
			return ['status' => 0, 'msg' => __('Nonce校验失败', 'wnd')];
		}

		/**
		 *已登录用户，且账户已绑定邮箱/手机，且验证类型不为bind（切换绑定邮箱）
		 *发送验证码给当前账户
		 */
		if ($current_user->ID and $type != 'bind') {
			$email_or_phone = $is_email ? $current_user->user_email : wnd_get_user_phone($current_user->ID);
			if (!$email_or_phone) {
				return ['status' => 0, 'msg' => __('当前账户未绑定', 'wnd') . $text];
			}
		}

		// 检测对应手机或邮箱格式：防止在邮箱绑定中输入手机号，反之亦然
		if ($is_email and !is_email($email_or_phone)) {
			return ['status' => 0, 'msg' => __('邮箱地址无效', 'wnd')];
		} elseif (!$is_email and !wnd_is_phone($email_or_phone)) {
			return ['status' => 0, 'msg' => __('手机号码无效', 'wnd')];
		}

		try {
			$auth = Wnd_Auth::get_instance($email_or_phone);
			$auth->set_type($type);
			$auth->set_template($template);
			$auth->send();
			return ['status' => 1, 'msg' => __('发送成功，请注意查收', 'wnd')];
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}
	}
}
