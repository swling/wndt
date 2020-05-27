<?php
namespace Wnd\Model;

/**
 *@since 2019.10.25
 *站内财务信息
 *
 */
class Wnd_Finance {

	/**
	 *@since 2019.02.11 查询是否已经支付
	 *@param int 	$user_id 	用户ID
	 *@param int 	$object_id  Post ID
	 *
	 *@return bool 	是否已支付
	 **/
	public static function user_has_paid($user_id, $object_id) {
		if (!$user_id or !$object_id) {
			return false;
		}

		$user_has_paid = wp_cache_get($user_id . '-' . $object_id, 'wnd_has_paid');

		if (false === $user_has_paid) {
			$args = [
				'posts_per_page' => 1,
				'post_type'      => 'order',
				'post_parent'    => $object_id,
				'author'         => $user_id,
				'post_status'    => 'success',
			];

			// 不能将布尔值直接做为缓存结果，会导致无法判断是否具有缓存，转为整型 0/1
			$user_has_paid = empty(get_posts($args)) ? 0 : 1;
			wp_cache_set($user_id . '-' . $object_id, $user_has_paid, 'wnd_has_paid');
		}

		return ($user_has_paid === 1 ? true : false);
	}

	/**
	 *@since 2019.03.29 查询订单统计
	 *@param 	int 	$object_id 	商品ID
	 *
	 *@return 	int 	order count
	 **/
	public static function get_order_count($object_id) {

		// 删除15分钟前未完成的订单，并扣除订单统计
		$args = [
			'posts_per_page' => -1,
			'post_type'      => 'order',
			'post_parent'    => $object_id,
			'post_status'    => 'pending',
			'date_query'     => [
				[
					'column'    => 'post_date',
					'before'    => date('Y-m-d H:i:s', current_time('timestamp', $gmt = 0) - 900),
					'inclusive' => true,
				],
			],
		];
		foreach (get_posts($args) as $post) {
			/**
			 * 此处不直接修正order_count，而是在删除订单时，通过action修正order_count @see wnd_action_deleted_post
			 * 以此确保订单统计的准确性，如用户主动删除，或其他原因人为删除订单时亦能自动修正订单统计
			 */
			wp_delete_post($post->ID, $force_delete = true);
		}
		unset($post, $args);

		// 返回清理过期数据后的订单统计
		return wnd_get_post_meta($object_id, 'order_count') ?: 0;
	}

	/**
	 *@since 2019.03.29 增加订单统计
	 *
	 *@param 	int 	$object_id 	商品ID
	 *@param 	int 	$number 	增加的数目，可为负
	 **/
	public static function inc_order_count($object_id, $number) {
		return wnd_inc_wnd_post_meta($object_id, 'order_count', $number);
	}

	/**
	 * 充值成功 写入用户 字段
	 *
	 *@param 	int 	$user_id 	用户ID
	 *@param 	float 	$money 		金额
	 *
	 */
	public static function inc_user_money($user_id, $money) {
		$new_money = static::get_user_money($user_id) + $money;
		$new_money = number_format($new_money, 2, '.', '');
		wnd_update_user_meta($user_id, 'money', $new_money);

		// 整站按月统计充值和消费
		static::update_fin_stats($money, 'recharge');
	}

	/**
	 *获取用户账户金额
	 *@param 	int 	$user_id 	用户ID
	 *@return 	float 	用户余额
	 */
	public static function get_user_money($user_id) {
		$money = wnd_get_user_meta($user_id, 'money');
		$money = is_numeric($money) ? $money : 0;
		return number_format($money, 2, '.', '');
	}

	/**
	 *新增用户消费记录
	 *
	 *@param 	int 	$user_id 	用户ID
	 *@param 	float 	$money 		金额
	 *
	 */
	public static function inc_user_expense($user_id, $money) {
		$new_money = static::get_user_expense($user_id) + $money;
		$new_money = number_format($new_money, 2, '.', '');
		wnd_update_user_meta($user_id, 'expense', $new_money);

		// 整站按月统计充值和消费
		static::update_fin_stats($money, 'expense');
	}

	/**
	 *获取用户消费
	 *@param 	int 	$user_id 	用户ID
	 *@return 	float 	用户消费
	 *
	 */
	public static function get_user_expense($user_id) {
		$expense = wnd_get_user_meta($user_id, 'expense');
		$expense = is_numeric($expense) ? $expense : 0;
		return number_format($expense, 2, '.', '');
	}

	/**
	 *@since 2019.02.22
	 *写入用户佣金
	 *@param 	int 	$user_id 	用户ID
	 *@param 	float 	$money 		金额
	 */
	public static function inc_user_commission($user_id, $money) {
		wnd_inc_wnd_user_meta($user_id, 'commission', number_format($money, 2, '.', ''));
	}

	/**
	 *@since 2019.02.18 获取用户佣金
	 *@param 	int 	$user_id 	用户ID
	 *
	 *@return 	float 	用户佣金
	 */
	public static function get_user_commission($user_id) {
		$commission = wnd_get_user_meta($user_id, 'commission');
		$commission = is_numeric($commission) ? $commission : 0;
		return number_format($commission, 2, '.', '');
	}

	/**
	 *@since 2019.02.13
	 *文章价格
	 *@param 	int 	$user_id 	用户ID
	 *@return  	float 	两位数的价格信息 或者 0
	 */
	public static function get_post_price($post_id) {
		$price = wnd_get_post_meta($post_id, 'price') ?: get_post_meta($post_id, 'price', 1) ?: false;
		$price = is_numeric($price) ? number_format($price, 2, '.', '') : 0;
		return apply_filters('wnd_get_post_price', $price, $post_id);
	}

	/**
	 *@since 2019.02.12
	 *用户佣金分成
	 *@param 	int 	$post_id
	 *@return 	float 	佣金分成
	 */
	public static function get_post_commission($post_id) {
		$commission_rate = is_numeric(wnd_get_config('commission_rate')) ? wnd_get_config('commission_rate') : 0;
		$commission      = wnd_get_post_price($post_id) * $commission_rate;
		$commission      = number_format($commission, 2, '.', '');
		return apply_filters('wnd_get_post_commission', $commission, $post_id);
	}

	/**
	 *@since 初始化
	 *统计整站财务数据，当用户发生充值或消费行为时触发
	 *按月统计，每月生成两条post数据
	 *
	 *用户充值post_type:stats-re
	 *用户消费post_type:stats-ex
	 *
	 *写入前，按post type 和时间查询，如果存在记录则更新记录，否则写入一条记录
	 *
	 *@param float 	$money 	变动金额
	 *@param string $type 	数据类型：recharge/expense
	 *
	 **/
	protected static function update_fin_stats($money, $type) {
		switch ($type) {
		// 充值
		case 'recharge':
			$post_type = 'stats-re';
			break;

		// 消费
		case 'expense':
			$post_type = 'stats-ex';

			break;

		// 默认
		default:
			$post_type = '';
			break;
		}

		if (!$money or !$type) {
			return;
		}

		$year       = date('Y', time());
		$month      = date('m', time());
		$post_title = $year . '-' . $month . '-' . $post_type;
		$slug       = $post_title;
		$stats_post = wnd_get_post_by_slug($slug, $post_type, 'private');

		// 更新统计
		if ($stats_post) {
			$old_money = $stats_post->post_content;
			$new_money = $old_money + $money;
			$new_money = number_format($new_money, 2, '.', '');
			wp_update_post(['ID' => $stats_post->ID, 'post_content' => $new_money]);

			// 新增统计
		} else {
			$post_arr = [
				'post_author'  => 1,
				'post_type'    => $post_type,
				'post_title'   => $post_title,
				'post_content' => $money,
				'post_status'  => 'private',
				'post_name'    => $slug,
			];
			wp_insert_post($post_arr);
		}
	}
}
