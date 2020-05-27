<?php
namespace Wnd\Action;

use Wnd\Action\Wnd_Create_Order;

/**
 *付费阅读类
 *@param $_POST['post_id']  Post ID
 */
class Wnd_Pay_For_Reading extends Wnd_Action_Ajax {

	public static function execute(): array{
		$post_id = (int) $_POST['post_id'];
		$post    = get_post($post_id);
		$user_id = get_current_user_id();
		if (!$post) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		if (!$user_id) {
			return ['status' => 0, 'msg' => __('请登录', 'wnd')];
		}

		// 1、已付费
		if (wnd_user_has_paid($user_id, $post_id)) {
			return ['status' => 0, 'msg' => __('请勿重复操作', 'wnd')];
		}

		// 2、支付失败
		$order = Wnd_Create_Order::execute($post_id);
		if ($order['status'] === 0) {
			return $order;
		}

		// 付费后刷新页面
		return ['status' => 4, 'msg' => __('请稍后', 'wnd')];
	}
}
