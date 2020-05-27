<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.07.23 用户设置邮箱表单
 */
class Wnd_Bind_Email_Form extends Wnd_Module {

	public static function build() {
		$current_user = wp_get_current_user();
		if (!$current_user->ID) {
			return static::build_error_message(__('请登录', 'wnd'));
		}

		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		$form->set_form_title('<span class="icon"><i class="fa fa-at"></i></span>' . __('绑定邮箱', 'wnd'), true);

		// 如果当前用户更改邮箱，则需要验证密码，首次绑定不需要
		if ($current_user->data->user_email) {
			$form->add_text(
				[
					'value'    => $current_user->data->user_email,
					'disabled' => true,
				]
			);
			$form->add_user_password(__('密码', 'wnd'), __('密码', 'wnd'));
		}

		$form->add_email_verify('bind');
		$form->set_action('wnd_bind_account');
		$form->set_submit_button(__('保存', 'wnd'));
		$form->build();

		return $form->html;
	}
}
