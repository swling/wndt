<?php
namespace Wnd\Model;

use Exception;
use Wnd\Model\Wnd_Auth;

/**
 *@since 2019.11.26
 *用户绑定
 */
abstract class Wnd_Binder {

	protected $user;
	protected $password;
	protected $auth_code;
	protected $bound_object;

	public function __construct() {
		$this->user = wp_get_current_user();
		if (!$this->user->ID) {
			throw new Exception(__('请登录', 'wnd'));
		}
	}

	public static function get_instance($bound_object) {
		if (is_email($bound_object)) {
			return new Wnd_Binder_Email($bound_object);
		} elseif (wnd_is_phone($bound_object)) {
			return new Wnd_Binder_Phone($bound_object);
		}
	}

	/**
	 *设置当前账户密码
	 */
	public function set_password($password) {
		$this->password = $password;
	}

	/**
	 *设置验证码
	 */
	public function set_auth_code($auth_code) {
		$this->auth_code = $auth_code;
	}

	abstract public function bind();

	/**
	 *核对验证码并绑定
	 *
	 *可能抛出异常
	 */
	protected function verify_auth_code() {
		$auth = Wnd_Auth::get_instance($this->bound_object);
		$auth->set_type('bind');
		$auth->set_auth_code($this->auth_code);

		/**
		 * 通常，正常前端注册的用户，已通过了邮件或短信验证中的一种，已有数据记录，绑定成功后更新对应数据记录，并删除当前验证数据记录
		 * 删除时会验证该条记录是否绑定用户，只删除未绑定用户的记录
		 * 若当前用户没有任何验证绑定记录，删除本条验证记录后，会通过 wnd_update_user_email() / wnd_update_user_phone() 重新新增一条记录
		 */
		$auth->verify(true);
	}
}
