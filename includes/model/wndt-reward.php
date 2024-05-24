<?php
namespace Wndt\Model;

use Exception;
use Wnd\Getway\Wnd_Payment_Getway;
use Wnd\Model\Wnd_Transaction;

/**
 * 赞赏
 * @since 2021.04.27
 */
class Wndt_Reward extends Wnd_Transaction {

	protected $transaction_type = 'reward';

	/**
	 * 检测创建权限
	 * @since 0.9.51
	 */
	protected function check_create() {}

	/**
	 * 此方法用于补充、修改、核查外部通过方法设定的交易数据，组成最终写入数据库的数据。完整的交易记录构造如下所示：
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
	protected function generate_transaction_data() {
		if (!$this->object_id) {
			throw new Exception('Object ID 无效');
		}

		if (!$this->total_amount) {
			throw new Exception('获取金额失败');
		}

		/**
		 * 订单标题
		 */
		$this->subject = $this->subject ?: (__('赞赏：', 'wndt') . get_the_title($this->object_id));
	}

	/**
	 * 充值付款校验
	 * @since 2019.02.11
	 *
	 * @param  object		$this->transaction 	required 	WP Post Object
	 * @return int                        		WP Post ID
	 */
	protected function complete_transaction(): int {
		// 定义变量 本类中，标题方法添加了站点名称，用于支付平台。故此调用父类方法用于站内记录
		$ID           = $this->get_transaction_id();
		$user_id      = $this->get_user_id();
		$total_amount = $this->get_total_amount();
		$object_id    = $this->get_object_id();

		// 更新统计
		if ($object_id) {
			wnd_inc_wnd_post_meta($object_id, 'reward_count', 1);
		}

		if ($user_id) {
			// 注册用户：站内订单：扣除余额、更新消费；站外订单：仅更新消费
			if (Wnd_Payment_Getway::is_internal_payment($ID)) {
				wnd_inc_user_balance($user_id, $total_amount * -1, false);
			} else {
				wnd_inc_user_expense($user_id, $total_amount);
			}
		}

		return $ID;
	}
}
