<?php

namespace Wndt\Module;

use Wnd\Model\Wnd_Order_Props;
use Wnd\Module\Wnd_Module_Form;
use Wnd\View\Wnd_Form_WP;

/**
 * 商品购买表单
 * @since 0.8.73
 */
class Wndt_Keys_Form extends Wnd_Module_Form {

	// 配置表单
	protected static function configure_form(array $args = []): object {
		$defaults = [
			'post_id'          => 0,
		];
		$args = wp_parse_args($args, $defaults);
		$post_id = $args['post_id'];

		$post = get_post($post_id);
		if (!$post) {
			return __('ID 无效', 'wnd');
		}

		$keys = get_post_meta($post_id, 'secret_keys', true) ?: [];
		$text = implode("\n", $keys);

		// 构建表单
		$form = new Wnd_Form_WP(true);
		$form->set_form_title('keys 一行一个');
		$form->add_hidden('post_id', $post_id);
		$form->set_route('action', 'wndt_update_keys');
		$form->add_input_name('keys');
		$form->add_html('<div class="field"><textarea name="keys" placeholder="一行一个" rows="20" type="textarea" class="textarea">' . $text . '</textarea></div>');
		$form->set_submit_button('保存');
		return $form;
	}
}
