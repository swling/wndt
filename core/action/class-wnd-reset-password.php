<?php
namespace Wnd\Action;

use Exception;
use Wnd\Model\Wnd_Auth;

/**
 *@since 2019.02.10 用户找回密码
 *@param $_POST['phone'] ?? $_POST['_user_user_email'];
 *@param $_POST['auth_code']
 *@param $_POST['_user_new_pass']
 *@param $_POST['_user_new_pass_repeat']
 */
class Wnd_Reset_Password extends Wnd_Action_Ajax {

	public static function execute(): array{
		$email_or_phone      = $_POST['_user_user_email'] ?? $_POST['phone'] ?? null;
		$new_password        = $_POST['_user_new_pass'] ?? null;
		$new_password_repeat = $_POST['_user_new_pass_repeat'] ?? null;
		$auth_code           = $_POST['auth_code'];
		$is_user_logged_in   = is_user_logged_in();

		// 验证密码正确性
		if (strlen($new_password) < 6) {
			return ['status' => 0, 'msg' => __('密码不能低于6位', 'wnd')];

		} elseif ($new_password_repeat != $new_password) {
			return ['status' => 0, 'msg' => __('两次输入的新密码不匹配', 'wnd')];
		}

		//获取用户
		$user = $is_user_logged_in ? wp_get_current_user() : wnd_get_user_by($email_or_phone);
		if (!$user) {
			return ['status' => 0, 'msg' => __('账户未注册', 'wnd')];
		}

		/**
		 *此处不可用：Wnd_Auth::get_instance($email_or_phone)
		 *原因是，已登录用户也可通过邮箱手机重设密码，而此时表单不包含邮箱或手机字段，而是从数据库中读取当前账户邮箱或手机
		 *因此此处需要根据用户校验
		 *
		 *获取用户的方法：
		 *已登录用户则为当前用户
		 *未登录用户通过邮箱或手机获取
		 */
		try {
			$auth = Wnd_Auth::get_instance($user);
			$auth->set_type('reset_password');
			$auth->set_auth_code($auth_code);
			$auth->verify();

			reset_password($user, $new_password);
			return [
				'status' => $is_user_logged_in ? 4 : 1,
				'msg'    => __('密码修改成功', 'wnd') . '&nbsp;<a onclick="wnd_ajax_modal(\'wnd_login_form\');">' . __('登录', 'wnd') . '</a>',
			];
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}
	}
}
