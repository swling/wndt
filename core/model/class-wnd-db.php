<?php
namespace Wnd\Model;

use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *@since 2019.01.24 WndWP所需独立数据表
 */
class Wnd_DB {

	use Wnd_Singleton_Trait;

	private function __construct() {
		global $wpdb;

		// 用户数据
		$wpdb->wnd_users = $wpdb->prefix . 'wnd_users';

		// 标签关联分类
		$wpdb->wnd_terms = $wpdb->prefix . 'wnd_terms';
	}

	/**
	 *@since 2019.01.24
	 *创建插件数据表
	 */
	public static function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require ABSPATH . 'wp-admin/includes/upgrade.php';

		// 创建用户数据库
		$create_user_sql = "CREATE TABLE IF NOT EXISTS $wpdb->wnd_users (

			ID bigint(20) NOT NULL auto_increment,
			user_id bigint(20) NOT NULL,
			email varchar(100) NOT NULL,
			phone varchar(14) NOT NULL,
			code varchar(64) NOT NULL,
			open_id varchar(64) NOT NULL,
			time bigint(20) NOT NULL,
			PRIMARY KEY (ID),
			KEY user_id(user_id),
			KEY email(email),
			KEY phone(phone),
			KEY open_id(open_id)

			) $charset_collate;";

		dbDelta($create_user_sql);

		// 创建标签关联分类数据库
		$create_terms_sql = "CREATE TABLE IF NOT EXISTS $wpdb->wnd_terms (
        ID bigint(20) NOT NULL auto_increment,
        cat_id bigint(20) NOT NULL,
        tag_id bigint(20) NOT NULL,
        tag_taxonomy varchar(32) NOT NULL,
        count bigint(20) NOT NULL,
        PRIMARY KEY (ID),
        UNIQUE KEY cat_tag(cat_id,tag_id)

        ) $charset_collate;";
		dbDelta($create_terms_sql);
	}
}
