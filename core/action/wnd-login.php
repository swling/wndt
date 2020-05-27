<?php
namespace Wnd\Action;

/**
 *@since 2019.1.13 用户登录
 *@param $username = trim($_POST['_user_user_login']);
 *@param $password = $_POST['_user_user_pass'];
 *
 *@param $remember = $_POST['remember'] ?? 0;
 *@param $redirect_to = $_REQUEST['redirect_to'] ?? home_url();
 */
class Wnd_Login extends Wnd_Action_Ajax {

	public static function execute(): array{
		$username    = trim($_POST['_user_user_login']);
		$password    = $_POST['_user_user_pass'];
		$remember    = $_POST['remember'] ?? 0;
		$remember    = $remember == 1 ? true : false;
		$redirect_to = $_REQUEST['redirect_to'] ?? home_url();

		// 登录过滤挂钩
		$wnd_can_login = apply_filters('wnd_can_login', ['status' => 1, 'msg' => '']);
		if ($wnd_can_login['status'] === 0) {
			return $wnd_can_login;
		}

		// 可根据邮箱，手机，或用户名查询用户
		$user = wnd_get_user_by($username);
		if (!$user) {
			return ['status' => 0, 'msg' => __('用户不存在', 'wnd')];
		}

		// 账户已封禁
		if (wnd_has_been_banned($user->ID)) {
			return ['status' => 0, 'msg' => __('账户已被封禁', 'wnd')];
		}

		// 校验密码并登录
		if (wp_check_password($password, $user->data->user_pass, $user->ID)) {
			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID, $remember);
			if ($redirect_to) {
				return ['status' => 3, 'msg' => __('登录成功', 'wnd'), 'data' => ['redirect_to' => $redirect_to, 'user_id' => $user->ID]];
			} else {
				return ['status' => 4, 'msg' => __('登录成功', 'wnd'), 'data' => ['user_id' => $user->ID]];
			}

		} else {
			return ['status' => 0, 'msg' => __('密码错误', 'wnd')];
		}
	}
}
