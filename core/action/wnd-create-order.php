<?php
namespace Wnd\Action;

use Exception;
use Wnd\Model\Wnd_Order;
use Wnd\Model\Wnd_Recharge;

/**
 *@since 2019.10.02
 *创建订单
 *@param $_POST['post_id']  Post ID
 */
class Wnd_Create_Order extends Wnd_Action_Ajax {

	public static function execute($post_id = 0): array{
		$post_id = $post_id ?: ($_POST['post_id'] ?? 0);
		$post    = $post_id ? get_post($post_id) : false;
		$user_id = get_current_user_id();
		if (!$user_id) {
			return ['status' => 0, 'msg' => __('请登录', 'wnd')];
		}

		if (!$post) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		$wnd_can_create_order = apply_filters('wnd_can_create_order', ['status' => 1, 'msg' => ''], $post_id);
		if ($wnd_can_create_order['status'] === 0) {
			return $wnd_can_create_order;
		}

		// 写入消费数据
		try {
			static::check_create($post_id, $user_id);

			$order = new Wnd_Order();
			$order->set_object_id($post_id);
			$order->set_subject(get_the_title($post_id));
			$order->create($is_success = true);
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}

		// 文章作者新增资金
		$commission = (float) wnd_get_post_commission($post_id);
		if ($commission > 0) {
			try {
				$recharge = new Wnd_Recharge();
				$recharge->set_object_id($post->ID); // 设置佣金来源
				$recharge->set_user_id($post->post_author);
				$recharge->set_total_amount($commission);
				$recharge->create(true); // 直接写入余额
			} catch (Exception $e) {
				return ['status' => 1, 'msg' => $e->getMessage()];
			}
		}

		// 支付成功
		return ['status' => 1, 'msg' => __('支付成功', 'wnd')];
	}

	/**
	 *检测下单权限
	 */
	public static function check_create($post_id, $user_id) {
		if (!$post_id) {
			throw new Exception(__('ID无效', 'wnd'));
		}

		if (!$user_id) {
			throw new Exception(__('用户无效', 'wnd'));
		}

		$post_price    = wnd_get_post_price($post_id);
		$user_money    = wnd_get_user_money($user_id);
		$primary_color = 'is-' . wnd_get_config('primary_color');

		// 余额不足
		if ($post_price > $user_money) {
			$msg = '<p>' . __('当前余额：¥ ', 'wnd') . '<b>' . $user_money . '</b>&nbsp;&nbsp;' . __('本次消费：¥ ', 'wnd') . '<b>' . $post_price . '</b></p>';
			if (wnd_get_config('alipay_appid')) {
				$msg .= '<a class="button ' . $primary_color . '" href="' . wnd_order_link($post_id) . '">' . __('在线支付') . '</a>';
				$msg .= '&nbsp;&nbsp;';
			}
			$msg .= wnd_modal_button(__('余额充值', 'wnd'), 'wnd_user_recharge_form', '', $primary_color . ' is-outlined');

			throw new Exception($msg);
		}
	}
}
