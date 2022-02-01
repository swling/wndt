<?php
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

// ################################################################以下为功能型函数

// 更新文章时，删除已不在文章中的图片
// add_action('post_updated', 'wndt_clean_attached', 10, 3);
function wndt_clean_attached($post_ID, $post_after, $post_before) {
	// 获取当前文章所有图片
	$images = get_attached_media('image', $post_ID);
	//获取所有类型附件
	// $images=get_attached_media('',$post_ID);
	foreach ($images as $image) {
		// 排除缩略图
		if (get_post_thumbnail_id($post_ID) != $image->ID and wnd_get_post_meta($post_ID, '_thumbnail_id') != $image->ID) {

			$file = get_post_meta($image->ID, '_wp_attached_file', true);
			// 利用php字符串匹配函数查找更新后的文章中是否包含图片地址 需要保证插入的是原图，非裁剪过后的
			if (false === strpos($post_after->post_content, $file)) {
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
	if ('trash' == $post->post_status) {
		return;
	}

	/**
	 * 文件上传路径
	 */
	$uploads_dir = 'uploads.fenbu.net';

	$post_content = wnd_download_remote_images($post->post_content, $uploads_dir, $post_ID);
	if ($post_content != $post->post_content) {
		wp_update_post(['ID' => $post_ID, 'post_content' => $post_content]);
	}

}
