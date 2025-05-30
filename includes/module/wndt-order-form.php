<?php

namespace Wndt\Module;

use Wnd\Model\Wnd_Order_Props;
use Wnd\Module\Wnd_Module_Form;
use Wnd\View\Wnd_Form_WP;

/**
 * 商品购买表单
 * @since 0.8.73
 */
class Wndt_Order_Form extends Wnd_Module_Form {

	// 配置表单
	protected static function configure_form(array $args = []): object {
		$defaults = [
			'post_id'          => 0,
			'ajax'             => true,
			'checked'          => '',
		];
		$args = wp_parse_args($args, $defaults);
		extract($args);

		$post = get_post($post_id);
		if (!$post) {
			return __('ID 无效', 'wnd');
		}

		// 构建表单
		$form = new Wnd_Form_WP($ajax);
		$form->add_hidden('post_id', $post_id);

		if ($ajax) {
			$form->set_route('module', 'common/wnd_payment_form');
		} else {
			$form->set_action(wnd_get_dashboard_url(), 'GET');
			$form->add_hidden('module', 'common/wnd_payment_form');
		}

		$form->add_number(
			[
				'name'       => Wnd_Order_Props::$quantity_key,
				'value'      => 1,
				'step'       => 1,
				'min'        => 1,
				'required'   => 'required',
				'addon_left' => '<button class="button is-static">数量</button>',
			]
		);

		$form->add_text(
			[
				'label'    => '',
				'name'     => 'email',
				'required' => false,
				'addon_left' => '<button class="button is-static">邮箱</button>',
			]
		);
		$form->set_submit_button('立即购买');
		return $form;
	}
}
