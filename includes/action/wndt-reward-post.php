<?php
namespace Wndt\Action;

use Exception;
use Wnd\Action\Wnd_Action;
use Wnd\Model\Wnd_Payment;

/**
 *赞赏文章
 *@since 2021.04.27
 *
 */
class Wndt_Reward_Post extends Wnd_Action {

	public function execute(): array{
		$post_id         = (int) ($this->data['post_id'] ?? 0);
		$amount          = (float) ($this->data['custom_amount'] ?? ($this->data['amount'] ?? 0));
		$payment_gateway = $this->data['payment_gateway'] ?? '';

		if (!$payment_gateway) {
			throw new Exception(__('未定义支付方式', 'wndt'));
		}

		if (!$amount) {
			throw new Exception(__('赞赏金额无效', 'wndt'));
		}

		$payment = Wnd_Payment::get_instance($payment_gateway);
		$payment->set_type('reward');
		$payment->set_object_id($post_id);
		$payment->set_total_amount($amount);
		$payment->create(false);

		// Ajax 提交时，需将提交响应返回，并替换用户UI界面，故需设置 ['status' => 7];
		$interface = $payment->build_interface();
		return ['status' => 7, 'data' => '<div class="has-text-centered">' . $interface . '</div>'];
	}
}
