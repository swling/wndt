<?php
namespace Wnd\Hook;

use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *WP Filter
 */
class Wnd_Add_Filter_WP {

	use Wnd_Singleton_Trait;

	private function __construct() {
		add_filter('wp_handle_upload_prefilter', [__CLASS__, 'filter_limit_upload']);
		add_filter('get_edit_post_link', [__CLASS__, 'filter_edit_post_link'], 10, 3);
		add_filter('wp_insert_post_data', [__CLASS__, 'filter_wp_insert_post_data'], 10, 1);
		add_filter('wp_insert_attachment_data', [__CLASS__, 'filter_wp_insert_attachment_data'], 10, 2);
		add_filter('get_comment_author_url', [__CLASS__, 'filter_comment_author_url'], 1, 3);
		add_filter('get_avatar', [__CLASS__, 'filter_avatar'], 1, 5);
	}

	/**
	 *@since 2019.01.16
	 * 限制wp editor上传附件
	 */
	public static function filter_limit_upload($file) {
		// 上传体积限制
		$image_size = $file['size'] / 1024;
		$limit      = wnd_get_config('max_upload_size') ?: 2048;

		if ($image_size > $limit) {
			$file['error'] = '上传文件不得超过' . $limit . 'KB';
			return $file;
		}

		// 文件信息
		$info = pathinfo($file['name']);
		$ext  = isset($info['extension']) ? '.' . $info['extension'] : null;
		if (!$ext) {
			$file['error'] = '未能获取到文件拓展名';
			return $file;
		}

		// 重命名文件名为随机码：用于美化附件slug，同时实现基本的文件路径加密
		$file['name'] = uniqid('file') . $ext;

		return $file;
	}

	/**
	 *@since 2019.01.31 重写WordPress原生编辑链接到指定的页面
	 */
	public static function filter_edit_post_link($link, $post_id, $context) {
		if (is_admin()) {
			return $link;
		}

		$edit_page = (int) wnd_get_config('edit_page');
		if ($edit_page) {
			return add_query_arg(['action' => 'edit', 'post_id' => $post_id], get_permalink($edit_page));
		}
		return $link;
	}

	/**
	 *@since 2019.04.03
	 *apply_filters( 'wp_insert_post_data', $data, $postarr )
	 *防止插入相同标题文章时（功能型post），反复查询post name，故此设置为随机值
	 */
	public static function filter_wp_insert_post_data($data) {
		if (empty($data['post_name'])) {
			$data['post_name'] = uniqid();
		}

		return $data;
	}

	/**
	 *@since 2019.07.18
	 *$data = apply_filters( 'wp_insert_attachment_data', $data, $postarr );
	 *自动给上传的附件依次设置 menu_order
	 *
	 *menu order值为当前附属的post上传附件总次数
	 *@see wnd_action_add_attachment
	 */
	public static function filter_wp_insert_attachment_data($data, $postarr) {
		// 如果已经指定了menu order或者附件并未附属到post
		if ($data['menu_order'] or !$data['post_parent']) {
			return $data;
		}

		$menu_order         = wnd_get_post_meta($data['post_parent'], 'attachment_records') ?: 0;
		$data['menu_order'] = ++$menu_order;

		return $data;
	}

	/**
	 *@since 2019.01.16
	 *注册用户的评论链接到作者页面
	 */
	public static function filter_comment_author_url($url, $id, $comment) {
		if ($comment->user_id) {
			return get_author_posts_url($comment->user_id);
		}
		return $url;
	}

	/**
	 *@since 初始化
	 * 调用用户字段 avatar存储的图像id，或者avatar_url存储的图像地址做自定义头像，并添加用户主页链接
	 */
	public static function filter_avatar($avatar, $id_or_email, $size, $default, $alt) {

		// 默认头像
		$avatar_url = wnd_get_config('default_avatar_url') ?: WND_URL . 'static/images/avatar.jpg';

		// 获取用户 ID
		if (is_numeric($id_or_email)) {
			$user_id = (int) $id_or_email;
			//评论获取
		} elseif (is_object($id_or_email)) {
			$user_id = (int) $id_or_email->user_id ?? 0;
			// 邮箱获取
		} else {
			$user    = get_user_by('email', $id_or_email);
			$user_id = $user ? $user->ID : 0;
		}
		$user_id = $user_id ?? 0;

		//已登录用户调用字段头像
		if ($user_id) {
			if (wnd_get_user_meta($user_id, 'avatar')) {
				$avatar_id  = wnd_get_user_meta($user_id, 'avatar');
				$avatar_url = wp_get_attachment_url($avatar_id) ?: $avatar_url;
				/**
				 *@since 2019.07.23
				 * 统一按阿里云oss裁剪缩略图
				 */
				$avatar_url = wnd_get_thumbnail_url($avatar_url, $size, $size);

			} elseif (wnd_get_user_meta($user_id, 'avatar_url')) {
				$avatar_url = wnd_get_user_meta($user_id, 'avatar_url') ?: $avatar_url;

			}

		}

		//头像
		$avatar = "<img alt='{$alt}' src='$avatar_url' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

		//注册用户，添加链接
		if ($user_id and !is_admin()) {
			$author_url = get_author_posts_url($user_id);
			$avatar     = sprintf(
				'<a href="%s" rel="external nofollow" class="url">%s</a>',
				$author_url,
				$avatar
			);
		}

		return $avatar;
	}
}
