<?php
namespace Wndt\Model;

use Exception;
use Wnd\Model\Wnd_Transaction;
use WP_Post;

/**
 *@since 2021.04.27
 *赞赏
 */
class Wndt_Reward extends Wnd_Transaction {
	/**
	 *@since 2019.02.11
	 *用户本站消费数据(含余额消费，或直接第三方支付消费)
	 *
	 *@param int 		$this->user_id  		required
	 *@param int 		$this->object_id  		option
	 *@param string 	$this->subject 			option
	 *@param string 	$this->payment_gateway	option 	支付平台标识
	 *@param bool 	 	$is_completed 			option 	是否直接写入，无需支付平台校验
	 *
	 *@return object WP Post Object
	 */
	protected function insert_record(bool $is_completed): WP_Post {
		// 处理匿名订单属性
		if (!$this->user_id) {
			/**
			 *匿名订单用户均为0，不可短时间内复用订单记录，或者会造成订单冲突
			 *更新自动草稿时候，modified 不会变需要查询 post_date
			 *@see get_posts()
			 *@see wp_update_post
			 */
			$date_query = [
				[
					'column' => 'post_date',
					'before' => date('Y-m-d H:i', current_time('timestamp') - 86400),
				],
			];
		} else {
			$date_query = [];
		}

		/**
		 *订单状态及标题
		 */
		$this->subject = $this->subject ?: (__('赞赏：', 'wndt') . get_the_title($this->object_id));
		$this->status  = $is_completed ? static::$completed_status : static::$processing_status;

		/**
		 *@since 2019.03.31 查询符合当前条件，但尚未完成的付款订单
		 */
		$old_orders = get_posts(
			[
				'author'         => $this->user_id,
				'post_parent'    => $this->object_id,
				'post_status'    => static::$processing_status,
				'post_type'      => 'reward',
				'posts_per_page' => 1,
				'date_query'     => $date_query,
			]
		);
		if ($old_orders) {
			$ID = $old_orders[0]->ID;
		}

		$post_arr = [
			'ID'           => $ID ?? 0,
			'post_author'  => $this->user_id,
			'post_parent'  => $this->object_id,
			'post_content' => $this->total_amount,
			'post_excerpt' => $this->payment_gateway,
			'post_status'  => $this->status,
			'post_title'   => $this->subject,
			'post_type'    => 'reward',
			'post_name'    => uniqid(),
		];
		$ID = wp_insert_post($post_arr);
		if (is_wp_error($ID) or !$ID) {
			throw new Exception(__('创建赞赏订单失败', 'wndt'));
		}

		// 构建Post
		return get_post($ID);
	}

	/**
	 *@since 2019.02.11
	 *更新支付订单状态
	 *
	 *@param object 	$this->transaction	required 	订单记录Post
	 *@param string 	$this->subject 		option
	 *
	 *@return true
	 */
	protected function verify_transaction(): bool {
		if ('reward' != $this->get_type()) {
			throw new Exception(__('赞赏 ID 无效', 'wndt'));
		}

		// 订单支付状态检查
		if (static::$processing_status != $this->get_status()) {
			throw new Exception(__('赞赏订单状态无效', 'wndt'));
		}

		$post_arr = [
			'ID'          => $this->get_transaction_id(),
			'post_status' => static::$completed_status,
			'post_title'  => $this->subject ?: $this->get_subject(),
		];
		$ID = wp_update_post($post_arr);
		if (!$ID or is_wp_error($ID)) {
			throw new Exception(__('数据更新失败', 'wndt'));
		}

		return true;
	}

	/**
	 *@since 2019.02.11
	 *充值付款校验
	 *@return int 		WP Post ID
	 *
	 *@param object		$this->transaction 	required 	WP Post Object
	 */
	protected function complete(): int{
		// 定义变量 本类中，标题方法添加了站点名称，用于支付平台。故此调用父类方法用于站内记录
		$ID        = $this->get_transaction_id();
		$object_id = $this->get_object_id();

		// 更新统计
		if ($object_id) {
			wnd_inc_wnd_post_meta($object_id, 'reward_count', 1);
		}

		return $ID;
	}
}
