<?php
namespace Wnd\Hook;

use Exception;
use Wnd\Model\Wnd_Auth;
use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *Wnd Filter
 */
class Wnd_Add_Filter {

	use Wnd_Singleton_Trait;

	private function __construct() {
		add_filter('wnd_can_reg', [__CLASS__, 'filter_can_reg'], 10, 1);
		add_filter('wnd_can_update_profile', [__CLASS__, 'filter_can_update_profile'], 10, 1);
		add_filter('wnd_insert_post_status', [__CLASS__, 'filter_post_status'], 10, 3);
		add_filter('wnd_safe_action_return', [__CLASS__, 'filter_safe_action_return'], 10, 1);
	}

	/**
	 *@since 2019.01.22
	 *检测当前信息是否可以注册新用户
	 */
	public static function filter_can_reg($can_array) {
		if (!get_option('users_can_register')) {
			return ['status' => 0, 'msg' => __('站点已关闭注册', 'wnd')];
		}

		// 验证:手机或邮箱 验证码
		$auth_code      = $_POST['auth_code'];
		$email_or_phone = $_POST['phone'] ?? $_POST['_user_user_email'] ?? '';
		try {
			$auth = Wnd_Auth::get_instance($email_or_phone);
			$auth->set_type('register');
			$auth->set_auth_code($auth_code);
			$auth->verify();
			return $can_array;
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}
	}

	/**
	 *@since  2020.03.26
	 *
	 *用户显示昵称不得与登录名重复
	 */
	public static function filter_can_update_profile($can_array) {
		$display_name = $_POST['_user_display_name'] ?? '';
		$user_login   = wp_get_current_user()->data->user_login ?? '';
		if ($display_name == $user_login) {
			$can_array = ['status' => 0, 'msg' => __('名称不得与登录名一致', 'wnd')];
		}

		return $can_array;
	}

	/**
	 *@since 2019.02.13
	 *文章状态过滤，允许前端表单设置为草稿状态（执行顺序10，因而会被其他顺序晚于10的filter覆盖）
	 *如果 $update_id 为0 表示为新发布文章，否则为更新文章
	 */
	public static function filter_post_status($post_status, $post_type, $update_id) {
		// 允许用户设置为草稿
		if (isset($_POST['_post_post_status']) and $_POST['_post_post_status'] == 'draft') {
			return 'draft';
		}

		// 管理员通过
		if (wnd_is_manager()) {
			return 'publish';
		}

		return $post_status;
	}

	/**
	 *Wnd_Safe_Action
	 *前端可直接向rest api发起：wnd_safe_action操作（对应nonce已提前生成），以执行一些请求或非敏感类操作
	 *由于do_action 没有返回值，无法对响应的操作返回消息给前端，故此用filter替代操作
	 *WP中filter与action的底层实质相同
	 *
	 *@since 2019.01.16
	 *@param $_REQUEST['post_id']
	 *@param $_REQUEST['method']
	 *@param $_REQUEST['param']
	 *
	 *@since 2020.04.18
	 *@see Wnd\Action\Wnd_Safe_Action
	 *return apply_filters('wnd_safe_action_return', ['status' => 0, 'msg' => __('默认安全 safe action 响应消息')]);
	 *
	 */
	public static function filter_safe_action_return($default_msg): array{
		if ('update_views' != $_REQUEST['method']) {
			return $default_msg;
		}

		$post_id = (int) $_REQUEST['param'];
		if (!$post_id) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		// 更新字段信息
		if (wnd_inc_post_meta($post_id, 'views', 1)) {
			do_action('wnd_update_views', $post_id);
			return ['status' => 1, 'msg' => time()];

			//字段写入失败，清除对象缓存
		} else {
			wp_cache_delete($post_id, 'post_meta');
			return ['status' => 0, 'msg' => time()];
		}
	}
}
