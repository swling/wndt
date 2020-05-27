<?php
namespace Wnd\Model;

use Exception;

/**
 *@since 2019.09.25
 *短信抽象类
 */
abstract class Wnd_Sms {
	// api属性
	protected $app_id;
	protected $app_key;
	protected $sign_name;

	// 短信实例属性
	protected $phone;
	protected $template;

	// 验证码属性
	protected $code;
	protected $valid_time;

	public function __construct() {
		$this->app_id    = wnd_get_config('sms_appid');
		$this->app_key   = wnd_get_config('sms_appkey');
		$this->sign_name = wnd_get_config('sms_sign');
	}

	// 实例化
	public static function get_instance() {
		// 获取短信服务商
		$sms_sp = wnd_get_config('sms_sp');

		if ('tx' == $sms_sp) {
			return new Wnd_Sms_TX();
		} elseif ('ali' == $sms_sp) {
			return new Wnd_Sms_Ali();
		} else {
			throw new Exception(__('指定短信服务商未完成配置', 'wnd'));
		}
	}

	public function set_phone($phone) {
		$this->phone = $phone;
	}

	public function set_template($template) {
		$this->template = $template;
	}

	/**
	 *验证码
	 *需请配合短信模板使用
	 */
	public function set_code($code) {
		$this->code = $code;
	}

	/**
	 *验证码有效时间
	 *需请配合短信模板使用
	 */
	public function set_valid_time($valid_time) {
		$this->valid_time = $valid_time;
	}

	/**
	 * 发送短信
	 */
	abstract public function send();
}
