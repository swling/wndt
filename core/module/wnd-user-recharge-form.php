<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_WP;

/**
 *@since 2019.01.21 充值表单
 */
class Wnd_User_Recharge_Form extends Wnd_Module {

	public static function build() {
		if (!wnd_get_config('alipay_appid')) {
			static::build_error_message(__('未设置支付接口', 'wnd'));
		}

		$form = new Wnd_Form_WP(false);
		$form->add_html('<div class="has-text-centered">');
		$form->add_radio(
			[
				'name'     => 'total_amount',
				'options'  => ['0.01' => '0.01', '10' => '10', '100' => '100', '200' => '200', '500' => '500'],
				'required' => 'required',
				'class'    => 'is-checkradio is-danger',
			]
		);
		$form->add_html('<img src="https://t.alipayobjects.com/images/T1HHFgXXVeXXXXXXXX.png">');
		$form->add_html('</div>');
		$form->set_action(wnd_get_do_url(), 'GET');
		$form->add_hidden('_wpnonce', wp_create_nonce('payment'));
		$form->add_hidden('action', 'payment');
		$form->set_submit_button(__('充值', 'wnd'));
		$form->build();

		return $form->html;
	}
}
