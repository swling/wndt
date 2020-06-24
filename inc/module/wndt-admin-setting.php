<?php
namespace Wndt\Module;

use Wnd\View\Wnd_Form_Option;

/**
 *列表模板
 */
class Wndt_Admin_Setting extends Wndt_Admin {

	public static function build() {
		$form = new Wnd_Form_Option('wndt', false);
		$form->add_image_upload('banner', 0, 0, 'Banner');

		$form->add_html('<div class="field is-horizontal"><div class="field-body">');
		$form->add_text(
			[
				'name'     => 'logo',
				'label'    => 'Logo',
				'required' => false,
			]
		);

		$form->add_number(
			[
				'name'     => 'gallery_picture_limit',
				'label'    => '产品相册图片',
				'required' => false,
			]
		);
		$form->add_html('</div></div>');

		$form->add_html('<div class="field is-horizontal"><div class="field-body">');
		$form->add_text(
			[
				'name'     => 'qq_appid',
				'label'    => 'QQ登录APP ID',
				'required' => false,
			]
		);

		$form->add_text(
			[
				'name'     => 'qq_appkey',
				'label'    => 'QQ登录APP KEY',
				'required' => false,
			]
		);
		$form->add_html('</div></div>');

		$form->add_url(
			[
				'name'     => 'social_redirect_url',
				'label'    => '社交登录回调地址',
				'required' => false,
			]
		);

		$form->add_html('<div class="field is-horizontal"><div class="field-body">');
		$form->add_url(
			[
				'name'     => 'social_redirect_url',
				'label'    => 'ICP备案号',
				'required' => false,
			]
		);

		$form->add_text(
			[
				'name'     => 'wangan',
				'label'    => '公安备案号',
				'required' => false,
			]
		);
		$form->add_html('</div></div>');

		$form->add_textarea(
			[
				'name'     => 'statistical_code',
				'label'    => '流量统计代码',
				'required' => false,
			]
		);

		$form->set_submit_button('保存', 'is-danger');
		$form->build();

		return $form->html;
	}
}
