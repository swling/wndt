<?php
namespace Wnd\Action;

use Exception;
use Wnd\Model\Wnd_Form_Data;

/**
 *@see README.md
 *ajax user POST name规则：
 *user field：_user_{field}
 *user meta：
 *_usermeta_{key} （*自定义数组字段）
 *_wpusermeta_{key} （*WordPress原生字段）
 *
 *@since 初始化 用户注册
 *@param $_POST['_user_user_login']
 *@param $_POST['_user_user_pass']
 *@param $_POST['_user_user_pass_repeat']
 *
 *@param $_POST['_user_user_email']
 *@param $_POST['_user_display_name']
 *@param $_POST['_wpusermeta_description']
 */
class Wnd_Reg extends Wnd_Action_Ajax {

	public static function execute(): array{
		try {
			$form_data = new Wnd_Form_Data();
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}

		// User Data
		$user_data = $form_data->get_user_data();
		if (isset($user_data['user_login'])) {
			if (strlen($user_data['user_login']) < 3) {
				return $value = ['status' => 0, 'msg' => __('用户名不能低于3位', 'wnd')];
			}
			if (is_numeric($user_data['user_login'])) {
				return $value = ['status' => 0, 'msg' => __('用户名不能是纯数字', 'wnd')];
			}

			// 未指定用户名：创建随机用户名
		} else {
			$user_data['user_login'] = wnd_generate_login();
		}

		if (strlen($user_data['user_pass']) < 6) {
			return $value = ['status' => 0, 'msg' => __('密码不能低于6位', 'wnd')];
		}

		if (isset($user_data['user_pass_repeat'])) {
			if ($user_data['user_pass_repeat'] !== $user_data['user_pass']) {
				return $value = ['status' => 0, 'msg' => __('两次输入的密码不匹配', 'wnd')];
			}
		}

		// 注册权限过滤挂钩
		$user_can_reg = apply_filters('wnd_can_reg', ['status' => 1, 'msg' => '']);
		if ($user_can_reg['status'] === 0) {
			return $user_can_reg;
		}

		// 写入新用户
		$user_id = wp_insert_user($user_data);
		if (is_wp_error($user_id)) {
			return ['status' => 0, 'msg' => $user_id->get_error_message()];
		}

		// 写入用户自定义数组meta
		$user_meta_data = $form_data->get_user_meta_data();
		if (!empty($user_meta_data)) {
			wnd_update_user_meta_array($user_id, $user_meta_data);
		}

		// 写入WordPress原生用户字段
		$wp_user_meta_data = $form_data->get_wp_user_meta_data();
		if (!empty($wp_user_meta_data)) {
			foreach ($wp_user_meta_data as $key => $value) {
				// 下拉菜单默认未选择时，值为 -1 。过滤
				if ($value !== '-1') {
					update_user_meta($user_id, $key, $value);
				}
			}
			unset($key, $value);
		}

		// 用户注册完成，自动登录
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);
		$redirect_to  = $_REQUEST['redirect_to'] ?? (wnd_get_config('reg_redirect_url') ?: home_url());
		$return_array = apply_filters(
			'wnd_reg_return',
			['status' => 3, 'msg' => __('注册成功', 'wnd'), 'data' => ['redirect_to' => $redirect_to, 'user_id' => $user_id]],
			$user_id
		);
		return $return_array;
	}
}
