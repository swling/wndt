<?php
namespace Wndt\Model;

/**
 *@since 2019.09.24
 *采购Post发布数目
 */
class Wndt_Admin {

	/**
	 *安装
	 */
	public static function install() {
		if (get_option('wndt')) {
			return;
		}

		// 默认option数据
		$default_option = [
			// 供需角色
			'enable_user_role'      => '0',

			// 内容设置
			'gallery_picture_limit' => '5',
			'max_cat_limit'         => '1',
		];

		update_option('wndt', $default_option);
	}

	/**
	 *卸载
	 */
	public static function uninstall() {
		return;
	}

	/**
	 *@since 2019.04.16
	 *清理过期数据
	 */
	public static function clean_up() {
		global $wpdb;

		// 两周前的事务
		$old_posts = $wpdb->get_col(
			"SELECT ID FROM $wpdb->posts WHERE post_type = 'transaction' AND post_status != 'pending' AND DATE_SUB(NOW(), INTERVAL 14 DAY) > post_date"
		);
		foreach ((array) $old_posts as $delete) {
			// Force delete.
			wp_delete_post($delete, true);
		}

		// 超期七天未完成的赞赏订单
		$old_posts = $wpdb->get_col(
			"SELECT ID FROM $wpdb->posts WHERE post_type  = 'reward' AND post_status = 'wnd-processing' AND DATE_SUB(NOW(), INTERVAL 7 DAY) > post_date"
		);
		foreach ((array) $old_posts as $delete) {
			// Force delete.
			wp_delete_post($delete, true);
		}
	}
}
