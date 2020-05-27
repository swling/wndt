<?php
namespace Wnd\Hook;

use Wnd\Model\Wnd_Auth;
use Wnd\Model\Wnd_Tag_Under_Category;
use Wnd\Model\Wnd_User;
use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *WP Action
 */
class Wnd_Add_Action_WP {

	use Wnd_Singleton_Trait;

	private function __construct() {
		add_action('wp_loaded', [__CLASS__, 'action_on_wp_loaded'], 10);
		add_action('user_register', [__CLASS__, 'action_on_user_register'], 10, 1);
		add_action('deleted_user', [__CLASS__, 'action_on_delete_user'], 10, 1);
		add_action('before_delete_post', [__CLASS__, 'action_on_before_delete_post'], 10, 1);
		add_action('post_updated', [__CLASS__, 'action_on_post_updated'], 10, 3);
		add_action('add_attachment', [__CLASS__, 'action_on_add_attachment'], 10, 1);

		/**
		 *分类关联标签
		 */
		Wnd_Tag_Under_Category::add_hook();
	}

	/**
	 *@since 2020.04.30
	 *This action hook is fired once WordPress, all plugins, and the theme are fully loaded and instantiated.
	 *@link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_loaded
	 */
	public static function action_on_wp_loaded() {
		if (wnd_has_been_banned()) {
			wp_logout();
			wp_die('账户已被封禁', get_option('blogname'));
		}
	}

	/**
	 *@since 初始化 用户注册后
	 */
	public static function action_on_user_register($user_id) {
		// 注册类，将注册用户id写入对应数据表
		$email_or_phone = $_POST['phone'] ?? $_POST['_user_user_email'] ?? '';
		if (!$email_or_phone) {
			return;
		}

		// 绑定邮箱或手机
		$auth = Wnd_Auth::get_instance($email_or_phone);
		$auth->reset_code($user_id);
	}

	/**
	 *删除用户的附加操作
	 *@since 2018
	 */
	public static function action_on_delete_user($user_id) {
		// 删除Wnd_User对象缓存
		$wnd_user = Wnd_User::get_wnd_user($user_id);
		Wnd_User::clean_wnd_user_caches($wnd_user);

		// 删除自定义用户数据
		global $wpdb;
		$wpdb->delete($wpdb->wnd_users, ['user_id' => $user_id]);
	}

	/**
	 *@since 2019.03.28
	 *删除文章时附件操作
	 *
	 *@since 2019.10.20
	 *需要删除文章对应的子文章，需要定义在：before_delete_post，仅此时尚保留对应关系
	 */
	public static function action_on_before_delete_post($post_id) {
		$delete_post = get_post($post_id);

		/**
		 *删除附属文件
		 */
		$args = [
			'posts_per_page' => -1,
			'post_type'      => get_post_types(), //此处需要删除所有子文章，如果设置为 any，自定义类型中设置public为false的仍然无法包含，故获取全部注册类型
			'post_status'    => 'any',
			'post_parent'    => $post_id,
		];

		// 获取并删除
		foreach (get_posts($args) as $child) {
			wp_delete_post($child->ID, true);
		}
		unset($child);

		/**
		 *@since 2019.06.04 删除订单时，扣除订单统计字段
		 *@since 2019.07.03 删除订单时，删除user_has_paid缓存
		 */
		if ($delete_post->post_type == 'order') {
			wnd_inc_wnd_post_meta($delete_post->post_parent, 'order_count', -1, true);
			wp_cache_delete($delete_post->post_author . '-' . $delete_post->post_parent, 'wnd_has_paid');
		}
	}

	/**
	 *@since 2019.06.05
	 *文章更新
	 */
	public static function action_on_post_updated($post_ID, $post_after, $post_before) {
		/**
		 * @since 2019.06.05 邮件状态改变时删除邮件查询对象缓存
		 */
		if ($post_after->post_type == 'mail') {
			wp_cache_delete($post_after->post_author, 'wnd_mail_count');
		}
	}

	/**
	 *@since 2019.07.18
	 *do_action( 'add_attachment', $post_ID );
	 *新增上传附件时
	 */
	public static function action_on_add_attachment($post_ID) {
		$post = get_post($post_ID);

		/**
		 *记录附件children_max_menu_order、删除附件时不做修改
		 *记录值用途：读取后，自动给上传附件依次设置menu_order，以便按menu_order排序
		 *@see wnd_filter_wp_insert_attachment_data
		 *
		 *
		 *典型场景：
		 *删除某个特定附件后，需要新上传附件，并恢复原有排序。此时要求新附件menu_order与删除的附件一致
		 *通过wnd_attachment_form()上传文件，并编辑menu_order即可达到上述要求
		 */
		if ($post->post_parent) {
			wnd_inc_wnd_post_meta($post->post_parent, 'attachment_records');
		}
	}
}
