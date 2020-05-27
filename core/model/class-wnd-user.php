<?php
namespace Wnd\Model;

/**
 *@since 2019.10.25
 *用户
 */
class Wnd_User {

	/**
	 *@since 2019.11.06
	 *获取自定义用户对象
	 *
	 *主要数据：user_id、email、phone、open_id
	 */
	public static function get_wnd_user($user_id) {
		$user = wp_cache_get($user_id, 'wnd_users');
		if ($user) {
			return $user;
		}

		global $wpdb;
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->wnd_users WHERE user_id = %d LIMIT 1", $user_id));
		if ($user) {
			static::update_wnd_user_caches($user);
		}
		return $user;
	}

	/**
	 *@since 2019.07.23
	 *根据第三方网站获取的用户信息，注册或者登录到WordPress站点
	 *@param string $open_id 		第三方账号openID
	 *@param string $display_name 	用户名称
	 *@param string $avatar_url 	用户外链头像
	 *
	 **/
	public static function social_login($open_id, $display_name = '', $avatar_url = '') {
		//当前用户已登录，同步信息
		if (is_user_logged_in()) {
			$this_user   = wp_get_current_user();
			$may_be_user = static::get_user_by_openid($open_id);
			if ($may_be_user and $may_be_user->ID != $this_user->ID) {
				exit(__('OpenID已被其他账户占用', 'wnd'));
			}

			if ($avatar_url) {
				wnd_update_user_meta($this_user->ID, 'avatar_url', $avatar_url);
			}
			if ($open_id) {
				static::update_user_openid($this_user->ID, $open_id);
			}
			wp_redirect(static::get_reg_redirect_url());
			exit;
		}

		//当前用户未登录，注册或者登录 检测是否已注册
		$user = static::get_user_by_openid($open_id);
		if (!$user) {
			$user_login = wnd_generate_login();
			$user_pass  = wp_generate_password();
			$user_data  = ['user_login' => $user_login, 'user_pass' => $user_pass, 'display_name' => $display_name];
			$user_id    = wp_insert_user($user_data);

			if (is_wp_error($user_id)) {
				wp_die($user_id->get_error_message(), get_option('blogname'));
			} else {
				static::update_user_openid($user_id, $open_id);
			}
		}

		// 同步头像并登录
		$user_id = $user ? $user->ID : $user_id;
		wnd_update_user_meta($user_id, 'avatar_url', $avatar_url);
		wp_set_auth_cookie($user_id, true);
		wp_redirect(static::get_reg_redirect_url());
		exit();
	}

	/**
	 *@since 2019.01.26 根据用户id获取号码
	 *@param 	int 			$user_id
	 *@return 	string|false 	用户手机号或false
	 */
	public static function get_user_phone($user_id) {
		if (!$user_id) {
			return false;
		}

		return static::get_wnd_user($user_id)->phone ?? false;
	}

	/**
	 *@since 2019.11.06	根据用户id获取openid
	 *@param 	int 			$user_id
	 *@return 	string|false 	用户openid或false
	 */
	public static function get_user_openid($user_id) {
		if (!$user_id) {
			return false;
		}

		return static::get_wnd_user($user_id)->open_id ?? false;
	}

	/**
	 *@since 2019.01.28 根据邮箱，手机，或用户名查询用户
	 *@param 	string 			$email_or_phone_or_login
	 *@return 	object|false	WordPress user object on success
	 */
	public static function get_user_by($email_or_phone_or_login) {
		if (!$email_or_phone_or_login) {
			return false;
		}

		/**
		 *邮箱
		 */
		if (is_email($email_or_phone_or_login)) {
			return get_user_by('email', $email_or_phone_or_login);
		}

		/**
		 *手机或登录名
		 *
		 *若当前字符匹配手机号码格式，则优先使用手机号查询
		 *若查询到用户即返回
		 *最后返回用户名查询结果
		 *
		 *注意：
		 *强烈建议禁止用户使用纯数字作为用户名
		 *否则可能出现手机号码与用户名的混乱，造成同一个登录名，对应过个账户信息的问题
		 *
		 *本插件已禁用纯数字用户名：@see wnd_ajax_reg()
		 */
		if (wnd_is_phone($email_or_phone_or_login)) {
			// 查询对象缓存
			$user_id = wp_cache_get($email_or_phone_or_login, 'wnd_phone');
			if ($user_id) {
				return get_user_by('id', $user_id);
			}

			global $wpdb;
			$user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->wnd_users} WHERE phone = %s;", $email_or_phone_or_login));
			$user    = $user_id ? get_user_by('ID', $user_id) : false;
			if ($user) {
				// 设置对象缓存并返回用户
				wp_cache_set($email_or_phone_or_login, $user_id, 'wnd_phone');
				return $user;
			}

		} else {
			return get_user_by('login', $email_or_phone_or_login);
		}
	}

	/**
	 *@since 2019.07.11
	 *根据openID获取WordPress用户，用于第三方账户登录
	 *@param 	openID
	 *@return 	object|false 	（WordPress：get_user_by）
	 */
	public static function get_user_by_openid($openid) {
		// 查询对象缓存
		$user_id = wp_cache_get($openid, 'wnd_openid');
		if (false === $user_id) {
			global $wpdb;
			$user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->wnd_users} WHERE open_id = %s;", $openid));
			if ($user_id) {
				wp_cache_set($openid, $user_id, 'wnd_openid');
			}
		}

		return $user_id ? get_user_by('ID', $user_id) : false;
	}

	/**
	 *@since 2019.07.11
	 *写入用户open id
	 *@param 	int 	$user_id
	 *@param 	string 	$open_id
	 *@return 	int 	$wpdb->insert
	 */
	public static function update_user_openid($user_id, $openid) {
		global $wpdb;

		// 查询
		$user = static::get_wnd_user($user_id);

		// 更新
		if ($user) {
			if ($openid == $user->open_id) {
				return;
			}

			$db = $wpdb->update(
				$wpdb->wnd_users,
				['open_id' => $openid, 'time' => time()],
				['ID' => $user->ID],
				['%s', '%d'],
				['%d']
			);

			// 写入
		} else {
			$db = $wpdb->insert(
				$wpdb->wnd_users,
				['user_id' => $user_id, 'open_id' => $openid, 'time' => time()],
				['%d', '%s', '%d']
			);
		}

		// 更新用户缓存
		if ($db) {
			static::clean_wnd_user_caches($user);
		}

		return $db;
	}

	/**
	 *@since 2019.07.11
	 *更新用户电子邮箱 同时更新插件用户数据库email，及WordPress账户email
	 *@param 	int 	$user_id
	 *@param 	string 	$email
	 *@return 	int 	$wpdb->insert
	 */
	public static function update_user_email($user_id, $email) {
		global $wpdb;

		// 查询
		$user = static::get_wnd_user($user_id);

		// 更新
		if ($user) {
			$db = $wpdb->update(
				$wpdb->wnd_users,
				['email' => $email, 'time' => time()],
				['ID' => $user->ID],
				['%s', '%d'],
				['%d']
			);

			// 写入
		} else {
			$db = $wpdb->insert(
				$wpdb->wnd_users,
				['user_id' => $user_id, 'email' => $email, 'time' => time()],
				['%d', '%s', '%d']
			);
		}

		// 更新WordPress账户email
		if ($db) {
			wp_update_user(['ID' => $user_id, 'user_email' => $email]);

			static::clean_wnd_user_caches($user);
		}

		return $db;
	}

	/**
	 *@since 2019.07.11
	 *写入用户手机号码
	 *@param 	int 	$user_id
	 *@param 	string 	$phone
	 *@return 	int 	$wpdb->insert
	 */
	public static function update_user_phone($user_id, $phone) {
		global $wpdb;

		// 查询
		$user = static::get_wnd_user($user_id);

		// 更新
		if ($user) {
			$db = $wpdb->update(
				$wpdb->wnd_users,
				['phone' => $phone, 'time' => time()],
				['ID' => $user->ID],
				['%s', '%d'],
				['%d']
			);

			// 写入
		} else {
			$db = $wpdb->insert(
				$wpdb->wnd_users,
				['user_id' => $user_id, 'phone' => $phone, 'time' => time()],
				['%d', '%s', '%d']
			);
		}

		// 更新字段
		if ($db) {
			static::clean_wnd_user_caches($user);
		}

		return $db;
	}

	/**
	 *@since 2019.11.06
	 *更新缓存
	 *@param object $user Wnd_user表对象
	 */
	public static function update_wnd_user_caches($user) {
		if ($user->user_id ?? false) {
			wp_cache_set($user->open_id, $user->user_id, 'wnd_openid');
			wp_cache_set($user->phone, $user->user_id, 'wnd_phone');
			wp_cache_set($user->user_id, $user, 'wnd_users');
		}
	}

	/**
	 *@since 2019.11.06
	 *删除缓存
	 *@param object $user Wnd_user表对象
	 */
	public static function clean_wnd_user_caches($user) {
		if ($user->user_id ?? false) {
			wp_cache_delete($user->open_id, 'wnd_openid');
			wp_cache_delete($user->phone, 'wnd_phone');
			wp_cache_delete($user->user_id, 'wnd_users');
		}
	}

	/**
	 *@since 初始化 判断当前用户是否为管理员
	 *@param 	int 	$user_id
	 *@return 	bool
	 *用户角色为：管理员或编辑 返回 true
	 */
	public static function is_manager($user_id = 0) {
		$user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();

		$user_role = $user->roles[0] ?? false;
		if ($user_role == 'administrator' or $user_role == 'editor') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *@since 2020.04.30 判断当前用户是否已被锁定：wp user meta：status
	 *@param 	int 	$user_id
	 *@return 	bool
	 */
	public static function has_been_banned($user_id = 0) {
		$user_id = $user_id ?: get_current_user_id();
		$status  = get_user_meta($user_id, 'status', true);

		return 'banned' == $status ? true : false;
	}

	/**
	 *@since 初始化
	 *用户display name去重
	 *@param 	string 		$display_name
	 *@param 	int 		$exclude_id
	 *@return 	int|false
	 */
	public static function is_name_duplicated($display_name, $exclude_id = 0) {
		// 名称为空
		if (empty($display_name)) {
			return false;
		}

		global $wpdb;
		$results = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM $wpdb->users WHERE display_name = %s AND  ID != %d  limit 1",
			$display_name,
			$exclude_id
		));

		return $results ?: false;
	}

	/**
	 *@since 2019.02.25
	 *发送站内信
	 *@param 	int 	$to 		收件人ID
	 *@param 	string 	$subject 	邮件主题
	 *@param 	string 	$message 	邮件内容
	 *@return 	bool 	true on success
	 */
	public static function mail($to, $subject, $message) {
		if (!get_user_by('id', $to)) {
			return ['status' => 0, 'msg' => __('用户不存在', 'wnd')];
		}

		$postarr = [
			'post_type'    => 'mail',
			'post_author'  => $to,
			'post_title'   => $subject,
			'post_content' => $message,
			'post_status'  => 'pending',
			'post_name'    => uniqid(),
		];

		$mail_id = wp_insert_post($postarr);

		if (is_wp_error($mail_id)) {
			return false;
		} else {
			wp_cache_delete($to, 'wnd_mail_count');
			return true;
		}
	}

	/**
	 *获取最近的10封未读邮件
	 *@since 2019.04.11
	 *@return 	int 	用户未读邮件
	 */
	public static function get_mail_count() {
		$user_id = get_current_user_id();
		if (!$user_id) {
			return 0;
		}

		$user_mail_count = wp_cache_get($user_id, 'wnd_mail_count');
		if (false === $user_mail_count) {
			$args = [
				'posts_per_page' => 11,
				'author'         => $user_id,
				'post_type'      => 'mail',
				'post_status'    => 'pending',
			];

			$user_mail_count = count(get_posts($args));
			$user_mail_count = ($user_mail_count > 10) ? '10+' : $user_mail_count;
			wp_cache_set($user_id, $user_mail_count, 'wnd_mail_count');
		}

		return $user_mail_count ?: 0;
	}

	/**
	 *@since 2019.06.10
	 *获取用户面板允许的post types
	 *@return array 	文章类型数组
	 */
	public static function get_user_panel_post_types() {
		$post_types = get_post_types(['public' => true], 'names', 'and');
		// 排除页面/附件/站内信
		unset($post_types['page'], $post_types['attachment'], $post_types['mail']);
		return apply_filters('wnd_user_panel_post_types', $post_types);
	}

	/**
	 *@since 2020.04.11
	 *获取注册后跳转地址
	 */
	public static function get_reg_redirect_url() {
		return wnd_get_config('reg_redirect_url') ?: home_url();
	}

	/**
	 *@since 2020.04.11
	 *获取用户语言
	 *
	 *该语言不同于WP原生的get_user_locale
	 *WP原生存储与wp user meta；本插件存储与wnd user meta：目的是减少一行数据库记录
	 */
	public static function get_user_locale($user_id) {
		return wnd_get_user_meta($user_id, 'locale') ?: 'default';
	}
}
