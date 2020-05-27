<?php
namespace Wnd\Model;

use Exception;

/**
 *@since 2019.11.26
 *用户绑定邮箱或手机
 */
class Wnd_Binder_Email extends Wnd_Binder {

	public function __construct($bound_object) {
		parent::__construct();

		$this->bound_object = $bound_object;
	}

	/**
	 *核对验证码并绑定
	 */
	public function bind() {
		// 更改邮箱或手机需要验证当前密码、首次绑定不需要
		$old_bind = $this->user->data->user_email;
		if ($old_bind and !wp_check_password($this->password, $this->user->data->user_pass, $this->user->ID)) {
			throw new Exception(__('当前密码错误', 'wnd'));
		}

		// 核对验证码并绑定
		try {
			$this->verify_auth_code();

			$bind = wnd_update_user_email($this->user->ID, $this->bound_object);
			if (!$bind) {
				throw new Exception(__('未知错误', 'wnd'));
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
}
