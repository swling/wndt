<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_User;

/**
 *@since 2019.01.29
 *用户常规资料表单
 */
class Wnd_Profile_Form extends Wnd_Module {

	public static function build() {
		$current_user = wp_get_current_user();
		if (!$current_user->ID) {
			return static::build_error_message(__('请登录', 'wnd'));
		}

		$form = new Wnd_Form_User();
		// profile表单可能有较为复杂的编辑界面，阻止回车提交
		$form->add_form_attr('onsubmit', 'return false');
		$form->add_form_attr('onkeydown', 'if(event.keyCode==13){return false;}');

		/*头像上传*/
		$form->add_user_avatar();

		$form->add_html('<div class="field is-horizontal"><div class="field-body">');
		$form->add_user_display_name(__('名称', 'wnd'), __('名称', 'wnd'));
		$form->add_user_url(__('网站', 'wnd'), __('网站', 'wnd'));
		$form->add_html('</div></div>');

		$form->add_user_description(__('简介', 'wnd'), __('简介', 'wnd'));
		$form->set_action('wnd_update_profile');
		$form->set_submit_button(__('保存', 'wnd'));

		$form->set_filter(__CLASS__);
		$form->build();

		return $form->html;
	}
}
