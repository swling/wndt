<?php
namespace Wndt\Module;

use Wnd\Getway\Wnd_Payment_Getway;
use Wnd\Module\Wnd_Module_Form;
use Wnd\View\Wnd_Form_WP;

/**
 * 在线支付订单表单
 * 匿名支付订单默认启用人机验证
 * @since 2020.06.30
 */
class Wndt_Reward_Form extends Wnd_Module_Form {

	// 配置表单
	protected static function configure_form(array $args = []): object{
		/**
		 * 基础信息
		 */
		$post_id         = $args['post_id'] ?? 0;
		$user_id         = get_current_user_id();
		$user_money      = wnd_get_user_balance($user_id);
		$title           = get_the_title($post_id);
		$gateway_options = Wnd_Payment_Getway::get_gateway_options();
		if ($user_money > 0) {
			$gateway_options = array_merge(['余额支付' => 'internal'], $gateway_options);
		}

		$form = new Wnd_Form_WP(true, !$user_id);
		$form->set_form_title(__('赞赏', 'wndt'), true);
		$form->add_html('<div class="has-text-centered field">');
		$form->add_html('<p>《' . $title . '》</p>');

		$form->add_radio(
			[
				'name'    => 'total_amount',
				'options' => ['¥ 0.01' => 0.01, '¥ 1' => 1, '¥ 2' => 2, '¥ 5' => 5, '¥ 10' => 10, '¥ 50' => 50, '¥ 100' => 100],
				'class'   => 'is-checkradio is-danger',
			]
		);

		$form->add_number(
			[
				'name'        => 'custom_total_amount',
				'placeholder' => '自定义金额',
				'min'         => 0.01,
				'step'        => 0.01,
			]
		);

		$form->add_radio(
			[
				'name'     => 'payment_gateway',
				'options'  => $gateway_options,
				'required' => 'required',
				'checked'  => Wnd_Payment_Getway::get_default_gateway(),
				'class'    => 'is-checkradio is-danger',
			]
		);
		$form->add_html('</div>');
		$form->add_hidden('type', 'reward');
		$form->set_route('action', 'wnd_do_payment');

		/**
		 * 遍历参数信息并构建表单字段
		 */
		foreach ($args as $key => $value) {
			$form->add_hidden($key, $value);
		}

		$form->set_submit_button('确认赞赏');
		return $form;
	}
}
