<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.01.13 登录框
 *@since 2019.03.10 Wnd_Form_WP
 */
class Wnd_Login_Form extends Wnd_Module {

	public static function build() {
		if (is_user_logged_in()) {
			return static::build_error_message(__('已登录', 'wnd'));
		}

		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		$form->set_form_title('<span class="icon"><i class="fa fa-user"></i></span>' . __('登录', 'wnd'), true);
		$form->add_user_login(__('账号', 'wnd'), __('用户名、手机、邮箱', 'wnd'), true);
		$form->add_user_password(__('密码', 'wnd'), __('密码', 'wnd'));
		$form->add_checkbox(
			[
				'name'    => 'remember',
				'options' => [__('记住我', 'wnd') => '1'],
				'checked' => '1',
			]
		);
		$form->add_hidden('redirect_to', $_SERVER['HTTP_REFERER'] ?? home_url());
		$form->set_action('wnd_login');
		$form->set_submit_button(__('登录', 'wnd'));
		$form->set_filter(__CLASS__);
		$form->build();

		return $form->html;
	}
}
