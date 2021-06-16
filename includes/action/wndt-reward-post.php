<?php
namespace Wndt\Action;

use Wnd\Action\Wnd_Do_Pay;

/**
 * 赞赏文章
 * @since 2021.04.27
 */
class Wndt_Reward_Post extends Wnd_Do_Pay {

	public function execute(): array{
		$this->data['total_amount'] = (float) ($this->data['custom_amount'] ?: $this->data['amount']);
		$this->data['type']         = 'reward';

		return parent::execute();
	}
}
