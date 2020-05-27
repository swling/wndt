<?php
namespace Wnd\Action;

/**
 *账户状态
 *@since 2020.04.30
 *@param $_POST['user_id'];
 *
 *账户状态为本插件自定义功能，故添加两个do_action以便后期拓展功能：
 *do_action('wnd_ban_account', $user_id);
 *do_action('wnd_restore_account', $user_id);
 */
class Wnd_Update_Account_Status extends Wnd_Action_Ajax {

	public static function execute(): array{
		$user_id       = $_POST['user_id'] ?? 0;
		$status        = $_POST['status'] ?? false;
		$before_status = get_user_meta($user_id, 'status', true) ?: 'ok';

		if (!$user_id) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		if (!wnd_is_manager()) {
			return ['status' => 0, 'msg' => __('权限不足', 'wnd')];
		}

		// 未发生改变
		if ($status == $before_status) {
			return ['status' => 0, 'msg' => __('未发生改变', 'wnd')];
		}

		// 更新状态
		$action = update_user_meta($user_id, 'status', $status);
		if (!$action) {
			return ['status' => 0, 'msg' => __('操作失败', 'wnd')];
		}

		// 封禁账户Action
		if ('banned' == $status) {
			do_action('wnd_ban_account', $user_id);
			return ['status' => 1, 'msg' => __('账户已被封禁', 'wnd')];
		}

		// 恢复账户Action
		if ('ok' == $status and 'banned' == $before_status) {
			do_action('wnd_restore_account', $user_id);
			return ['status' => 1, 'msg' => __('账户已解封', 'wnd')];
		}
	}
}
