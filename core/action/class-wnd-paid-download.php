<?php
namespace Wnd\Action;

/**
 *@since 2019.02.12 文件校验下载
 *@param $_REQUEST['post_id']
 */
class Wnd_Paid_Download extends Wnd_Action {

	public static function execute() {
		$post_id = (int) $_REQUEST['post_id'];
		$user_id = get_current_user_id();
		$price   = get_post_meta($post_id, 'price', 1);
		$file_id = wnd_get_post_meta($post_id, 'file') ?: get_post_meta($post_id, 'file');
		$file    = get_attached_file($file_id, $unfiltered = false);
		if (!$file) {
			wp_die(__('获取文件失败', 'wnd'), get_option('blogname'));
		}

		//1、免费，或者已付费
		if (!$price or wnd_user_has_paid($user_id, $post_id)) {
			wnd_inc_wnd_post_meta($post_id, 'download_count', 1);
			return wnd_download_file($file, $post_id);
		}

		//2、 作者直接下载
		if (get_post_field('post_author', $post_id) == get_current_user_id()) {
			return wnd_download_file($file, $post_id);
		}

		// 校验失败
		wp_die(__('权限错误', 'wnd'), get_option('blogname'));
	}
}
