<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.01.28 找回密码
 *@param $type 	string	email/phone
 */
class Wnd_Reset_Password_Form extends Wnd_Module {

	public static function build($type = 'email') {
		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		if ('phone' == $type) {
			$form->set_form_title('<span class="icon"><i class="fa fa-phone-square"></i></span>' . __('重置密码', 'wnd'), true);
			$form->add_sms_verify('reset_password', wnd_get_config('sms_template_v'));
		} else {
			$form->set_form_title('<span class="icon"><i class="fa fa-at"></i></span>' . __('重置密码', 'wnd') . '</h3>', true);
			$form->add_email_verify('reset_password');
		}

		$form->add_user_new_password(__('新密码', 'wnd'), __('新密码', 'wnd'), true);
		$form->add_user_new_password_repeat(__('确认新密码', 'wnd'), __('确认新密码', 'wnd'), true);
		$form->set_action('wnd_reset_password');
		$form->set_submit_button(__('重置密码', 'wnd'));
		$form->set_filter(__CLASS__);
		$form->build();

		return $form->html;
	}
}
