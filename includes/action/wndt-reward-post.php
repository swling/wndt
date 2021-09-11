<?php
namespace Wndt\Action;

use Wnd\Action\Wnd_Do_Payment;

/**
 * 赞赏文章
 * @since 2021.04.27
 */
class Wndt_Reward_Post extends Wnd_Do_Payment {

	/**
	 * 支持手动填写金额
	 *
	 */
	protected function parse_data() {
		parent::parse_data();
		$this->total_amount = (float) ($this->data['custom_amount'] ?: $this->data['amount']);
	}
}
