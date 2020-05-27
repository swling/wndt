<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.01.21 注册表单
 *@param $type 	string	email/phone
 */
class Wnd_Reg_Form extends Wnd_Module {

	public static function build($type = 'email') {
		// 设定默认值
		$type = $type ?: (wnd_get_config('enable_sms') ? 'phone' : 'email');

		// 已登录
		if (is_user_logged_in()) {
			return static::build_error_message(__('已登录', 'wnd'));
		}

		//已关闭注册
		if (!get_option('users_can_register')) {
			return static::build_error_message(__('站点已关闭注册', 'wnd'));
		}

		//未开启手机验证
		if ('phone' == $type and wnd_get_config('enable_sms') != 1) {
			return static::build_error_message(__('当前未配置短信验证', 'wnd'));
		}

		// 关闭了邮箱注册（强制手机验证）
		if ('email' == $type and 1 == wnd_get_config('disable_email_reg')) {
			return static::build_error_message(__('当前设置禁止邮箱注册', 'wnd'));
		}

		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		$form->set_form_title('<span class="icon"><i class="fa fa-user"></i></span>' . __('注册', 'wnd'), true);

		/**
		 *注册用户通常为手机验证，或邮箱验证，为简化注册流程，可选择禁用用户名字段
		 *后端将随机生成用户名，用户可通过邮箱或手机号登录
		 */
		if (wnd_get_config('disable_user_login') != 1) {
			$form->add_user_login(__('用户名', 'wnd'), __('用户名', 'wnd'));
		}
		$form->add_user_password(__('密码', 'wnd'), __('密码', 'wnd'));

		if ($type == 'phone') {
			$form->add_sms_verify('register', wnd_get_config('sms_template_r'));
		} else {
			$form->add_email_verify('register');
		}
		if (wnd_get_config('agreement_url') or 1) {
			$text = __('已阅读并同意', 'wnd') . '<a href="' . wnd_get_config('agreement_url') . '" target="_blank">' . __('《注册协议》', 'wnd') . '</a>';
			$form->add_checkbox(
				[
					'name'     => 'agreement',
					'options'  => [$text => 1],
					'checked'  => 1,
					'required' => 'required',
				]
			);
		}

		$form->set_action('wnd_reg');
		$form->set_submit_button(__('注册', 'wnd', 'wnd'));
		// 以当前函数名设置filter hook
		$form->set_filter(__CLASS__);
		$form->build();

		return $form->html;
	}
}
