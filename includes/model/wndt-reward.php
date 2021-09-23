<?php
namespace Wndt\Model;

use Wnd\Getway\Wnd_Payment_Getway;
use Wnd\Model\Wnd_Transaction;

/**
 * 赞赏
 * @since 2021.04.27
 */
class Wndt_Reward extends Wnd_Transaction {

	protected $transaction_type = 'reward';

	/**
	 * 按需对如下数据进行构造：
	 *
	 * $post_arr = [
	 *     'ID'           => $this->transaction_id,
	 *     'post_type'    => $this->transaction_type,
	 *     'post_author'  => $this->user_id,
	 *     'post_parent'  => $this->object_id,
	 *     'post_content' => $this->total_amount,
	 *     'post_excerpt' => $this->payment_gateway,
	 *     'post_status'  => $this->status,
	 *     'post_title'   => $this->subject,
	 *     'post_name'    => $this->transaction_slug ?: uniqid(),
	 * ];
	 *
	 * @since 0.9.32
	 */
	protected function generate_transaction_data(bool $is_completed) {
		/**
		 * 订单状态及标题
		 */
		$this->subject = $this->subject ?: (__('赞赏：', 'wndt') . get_the_title($this->object_id));
		$this->status  = $is_completed ? static::$completed_status : static::$processing_status;

		/**
		 * @since 2019.03.31 查询符合当前条件，但尚未完成的付款订单
		 */
		$this->transaction_id = $this->get_reusable_transaction_id();
	}

	/**
	 * 充值付款校验
	 * @since 2019.02.11
	 *
	 * @param  object		$this->transaction 	required 	WP Post Object
	 * @return int                        		WP Post ID
	 */
	protected function complete(): int{
		// 定义变量 本类中，标题方法添加了站点名称，用于支付平台。故此调用父类方法用于站内记录
		$ID           = $this->get_transaction_id();
		$user_id      = $this->get_user_id();
		$total_amount = $this->get_total_amount();
		$object_id    = $this->get_object_id();

		// 更新统计
		if ($object_id) {
			wnd_inc_wnd_post_meta($object_id, 'reward_count', 1);
		}

		// 站内直接消费，无需支付平台支付校验，记录扣除账户余额、在线支付则不影响当前余额
		if (Wnd_Payment_Getway::is_internal_payment($ID)) {
			wnd_inc_user_money($user_id, $total_amount * -1, false);
		}

		return $ID;
	}
}
