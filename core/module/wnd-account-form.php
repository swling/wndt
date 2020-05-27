<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.01.23 用户更新账户表单
 */
class Wnd_Account_Form extends Wnd_Module {

	public static function build() {
		$user       = wp_get_current_user();
		$enable_sms = wnd_get_config('enable_sms');
		if (!$user->data->ID) {
			return static::build_error_message(__('请登录', 'wnd'));
		}

		/**
		 *如果当前账户为社交登录，账户设置前必须绑定邮箱或手机
		 */
		if (!$user->data->user_email and !wnd_get_user_phone($user->data->ID)) {
			$message = '<p>' . __('出于安全考虑，请绑定邮箱或手机') . '</p>';
			$message .= wnd_modal_button(__('绑定邮箱'), 'wnd_bind_email_form');

			if ($enable_sms) {
				$message .= '&nbsp;&nbsp;';
				$message .= wnd_modal_button(__('绑定手机'), 'wnd_bind_phone_form');
			}
			return static::build_error_message(__($message, 'wnd'));
		}

		$form = new Wnd_Form_User();
		$form->add_form_attr('class', 'user-form');
		$form->add_user_password(__('密码', 'wnd'), __('当前密码', 'wnd'));
		$form->add_user_new_password(__('新密码', 'wnd'), __('新密码', 'wnd'));
		$form->add_user_new_password_repeat(__('确认新密码', 'wnd'), __('确认新密码', 'wnd'));
		$form->set_action('wnd_update_account');
		$form->set_submit_button(__('保存', 'wnd'));
		$form->set_filter(__CLASS__);
		$form->build();

		/**
		 *@since 2019.09.19
		 *绑定邮箱或手机
		 */
		$html = '<div class="has-text-centered">';
		$html .= '<a onclick="wnd_ajax_modal(\'wnd_bind_email_form\')">' . __('邮箱设置', 'wnd') . '</a> | ';
		$html .= $enable_sms ? '<a onclick="wnd_ajax_modal(\'wnd_bind_phone_form\')">' . __('手机设置', 'wnd') . '</a> | ' : '';
		$html .= '<a onclick="wnd_ajax_modal(\'wnd_user_center\',\'do=reset_password\')">' . __('重置密码', 'wnd') . '</a>';
		$html .= '</div>';

		return $form->html . $html;
	}
}
