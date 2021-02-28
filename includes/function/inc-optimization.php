<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

//移除网址表单、cookie选项
function url_filtered($fields) {
	if (isset($fields['url'])) {
		unset($fields['url']);
	}

	if (isset($fields['cookies'])) {
		unset($fields['cookies']);
	}

	return $fields;
}
add_filter('comment_form_default_fields', 'url_filtered');

//**********function 2
//移除多余头部信息，JavaScript，可能导致部分主题，插件失效
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head'); //去除标签中pre、next这样的标签

// 移除 api
// add_filter('rest_enabled', '__return_false');
// add_filter('rest_jsonp_enabled', '__return_false');

remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

//移除WordPress头部加载DNS预获取（dns-prefetch）
function remove_dns_prefetch($hints, $relation_type) {
	if ('dns-prefetch' === $relation_type) {
		return array_diff(wp_dependencies_unique_hosts(), $hints);
	}
	return $hints;
}
add_filter('wp_resource_hints', 'remove_dns_prefetch', 10, 2);

/**
 * Disable the emoji's
 */
function disable_emojis() {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
}
add_action('init', 'disable_emojis');

function disable_emojis_tinymce($plugins) {
	return array_diff($plugins, ['wpemoji']);
}

// 禁用加载内容时embed 查找，但如果是后台编辑文章中包含url 则仍然会添加 _oembed 类post meta
remove_filter('the_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
remove_action('wp_head', 'wp_oembed_add_host_js');

// ################################################################以下为功能型函数

// 只查看自己的附件

add_action('pre_get_posts', 'ml_restrict_media_library');
function ml_restrict_media_library($wp_query_obj) {
	global $current_user, $pagenow;
	if (!is_a($current_user, 'WP_User')) {
		return;
	}

	if ('admin-ajax.php' != $pagenow or $_REQUEST['action'] != 'query-attachments') {
		return;
	}

	if (!current_user_can('manage_media_library')) {
		$wp_query_obj->set('author', $current_user->ID);
	}

	return;
}

// 插入图片时 删除高宽 用于自适应布局
add_filter('post_thumbnail_html', 'remove_width_attribute', 10);
add_filter('image_send_to_editor', 'remove_width_attribute', 10);

function remove_width_attribute($html) {
	$html = preg_replace('/(width|height)="\d*"\s/', "", $html);
	return $html;
}

// 更新文章时，删除已不在文章中的图片
add_action('post_updated', 'wndt_clean_attached', 10, 3);
function wndt_clean_attached($post_ID, $post_after, $post_before) {

	// 排除profile类型
	// if (get_post_type($post_ID) == 'company' ) {
	// 	return;
	// }

	// 获取当前文章所有图片
	$images = get_attached_media('image', $post_ID);
	//获取所有类型附件
	// $images=get_attached_media('',$post_ID);
	foreach ($images as $image) {
		// 排除缩略图
		if (get_post_thumbnail_id($post_ID) != $image->ID and wnd_get_post_meta($post_ID, '_thumbnail_id') != $image->ID) {

			$file = get_post_meta($image->ID, '_wp_attached_file', true);
			// 利用php字符串匹配函数查找更新后的文章中是否包含图片地址 需要保证插入的是原图，非裁剪过后的
			if (strpos($post_after->post_content, $file) == false) {
				wp_delete_attachment($image->ID, 'true');
			}
		}
	}
	unset($image);
}

########################################### 文章写入后，对文章内容进行图片本地化保存
// do_action( 'wp_insert_post', $post_ID, $post, $update );
// add_action('wp_insert_post', 'wndt_insert_post_action', 10, 3);
function wndt_insert_post_action($post_ID, $post, $update) {

	if ($post->post_status == 'trash') {
		return;
	}

	/**
	 *文件上传路径
	 */
	$uploads_dir = 'uploads.fenbu.net';

	$post_content = wnd_download_remote_images($post->post_content, $uploads_dir, $post_ID);
	if ($post_content != $post->post_content) {
		wp_update_post(['ID' => $post_ID, 'post_content' => $post_content]);
	}

}
