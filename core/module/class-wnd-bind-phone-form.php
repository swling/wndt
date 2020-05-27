<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.07.23 用户设置邮箱表单
 */
class Wnd_Bind_Phone_Form extends Wnd_Module {

	public static function build() {
		$current_user = wp_get_current_user();
		if (!$current_user->ID) {
			return static::build_error_message(__('请登录', 'wnd'));
		}
		$current_user_phone = wnd_get_user_phone($current_user->ID);

		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		$form->set_form_title('<span class="icon"><i class="fa fa-phone"></i></span>' . __('绑定手机', 'wnd'), true);

		// 如果当前用户更改手机号，需要验证密码，首次绑定不需要
		if ($current_user_phone) {
			$form->add_text(
				[
					'value'    => $current_user_phone,
					'disabled' => true,
				]
			);
			$form->add_user_password(__('密码', 'wnd'), __('密码', 'wnd'));
		}

		$form->add_sms_verify('bind', wnd_get_config('sms_template_v'));
		$form->set_action('wnd_bind_account');
		$form->set_submit_button(__('保存', 'wnd'));
		$form->build();

		return $form->html;
	}
}
