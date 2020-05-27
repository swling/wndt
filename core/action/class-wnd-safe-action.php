<?php
namespace Wnd\Action;

/**
 *@since 2019.10.05
 *封装一些无关数据安全的常规操作
 *由于rest操作需要验证action nonce，因此在前端无法直接发起一个操作请求
 *本操作对应的nonce：wp_create_nonce('wnd_safe_action') 已提前生成，因此前端可以直接获取，从而调用本控制类
 *
 *请求必须包含以下参数：
 *@param $_REQUEST['action'] string 固定值：'wnd_safe_action'
 *@param $_REQUEST['method'] string 指定本类中的方法
 */
class Wnd_Safe_Action extends Wnd_Action_Ajax {

	// 根据method参数选择处理方法
	public static function execute(): array{
		$method = $_REQUEST['method'] ?? false;
		if (!$method) {
			return ['status' => 0, 'msg' => __('未指定方法', 'wnd')];
		}

		/**
		 *执行Action
		 *
		 */
		return apply_filters('wnd_safe_action_return', ['status' => 0, 'msg' => __('默认安全 safe action 响应消息')]);
	}
}
