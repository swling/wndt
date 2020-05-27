<?php
namespace Wnd\Action;

/**
 *删除用户
 *@since 2020.04.30
 *@param $_POST['user_id'];
 */
class Wnd_Delete_User extends Wnd_Action_Ajax {

	public static function execute(): array{
		$user_id = $_POST['user_id'] ?? 0;
		$confirm = $_POST['confirm'] ?? false;
		if (!$user_id) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		if (!is_super_admin()) {
			return ['status' => 0, 'msg' => __('权限不足', 'wnd')];
		}

		if (is_super_admin($user_id)) {
			return ['status' => 0, 'msg' => __('无法删除超级管理员', 'wnd')];
		}

		if (!$confirm) {
			return ['status' => 0, 'msg' => __('请确认操作', 'wnd')];
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';
		$action = wp_delete_user($user_id);
		if ($action) {
			return ['status' => 1, 'msg' => __('删除成功', 'wnd')];
		} else {
			return ['status' => 1, 'msg' => __('删除失败', 'wnd')];
		}
	}
}
