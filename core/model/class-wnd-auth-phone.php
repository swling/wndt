<?php
namespace Wnd\Model;

use Exception;
use Wnd\Model\Wnd_Sms;

/**
 *@since 2019.12.19
 *验证授权
 *
 *短信验证码
 */
class Wnd_Auth_phone extends Wnd_Auth {

	// 数据库字段：phone
	protected $db_field = 'phone';

	// 验证码有效时间（秒）
	protected $valid_time = 600;

	public function __construct($auth_object) {
		parent::__construct($auth_object);

		$this->template = wnd_get_config('sms_template_v');
	}

	/**
	 *@since 2019.02.10 权限检测
	 *
	 *@return true|exception
	 */
	protected function check_send() {
		parent::check_type();

		// 短信发送必须指定模板
		if (!$this->template) {
			throw new Exception(__('未指定短信模板', 'wnd'));
		}
	}

	/**
	 *@since 初始化
	 *通过ajax发送短信
	 *点击发送按钮，通过js获取表单填写的手机号，检测并发送短信
	 *@param string $this->auth_object 	邮箱或手机
	 *@param string $this->auth_code 		验证码
	 *@return true|exception
	 */
	public function send() {
		// 权限检测
		$this->check_send();

		// 写入手机记录
		if (!$this->insert()) {
			throw new Exception(__('数据库写入失败', 'wnd'));
		}

		// 发送短信
		$sms = Wnd_Sms::get_instance();
		$sms->set_phone($this->auth_object);
		$sms->set_code($this->auth_code);
		$sms->set_valid_time($this->valid_time / 60);
		$sms->set_template($this->template);
		$sms->send();
	}
}
