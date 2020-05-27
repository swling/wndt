<?php
namespace Wnd\Action;

/**
 *@since 2019.01.20
 *ajax文件上传
 *@param $_FILES
 *@param $_POST['save_width']
 *@param $_POST['save_height']
 *@param $_POST['thumbnail_width']
 *@param $_POST['thumbnail_height']
 *@param $_POST['meta_key']
 *@param $_POST['meta_key_nonce']
 *@param $_POST['post_parent']
 *
 *@return $return_array array 二维数组
 *	[
 *		[
 *			'status' => 1,
 *			'data' => ['url' => $url, 'thumbnail' => $thumbnail ?? 0, 'id' => $file_id],
 *			'msg' => '上传成功',
 *		],
 *	];
 *
 */
class Wnd_Upload_File extends Wnd_Action_Ajax {

	public static function execute(): array{
		//$_FILES['wnd_file']需要与input name 值匹配
		if (empty($_FILES['wnd_file'])) {
			return ['status' => 0, 'msg' => __('上传文件为空', 'wnd')];
		}

		$save_width       = $_POST['save_width'] ?? 0;
		$save_height      = $_POST['save_height'] ?? 0;
		$thumbnail_height = $_POST['thumbnail_height'] ?? 0;
		$thumbnail_width  = $_POST['thumbnail_width'] ?? 0;
		$meta_key         = $_POST['meta_key'] ?? null;
		$post_parent      = $_POST['post_parent'] ?? 0;
		$user_id          = get_current_user_id();

		// 上传信息校验
		if (!$user_id and !$post_parent) {
			return ['status' => 0, 'msg' => __('User ID及Post ID不可同时为空', 'wnd')];
		}

		/**
		 *@since 2019.05.08 上传文件meta_key post_parent校验
		 *meta_key 及 post_parent同时为空时，上传文件将成为孤立的的文件，在前端上传附件应该具有明确的用途，应避免这种情况
		 */
		if (!$meta_key and !$post_parent) {
			return ['status' => 0, 'msg' => __('Meta_key与Post_parent不可同时为空', 'wnd')];
		}

		if (!wp_verify_nonce($_POST['meta_key_nonce'], $meta_key)) {
			return ['status' => 0, 'msg' => __('meta_key不合法', 'wnd')];
		}

		if ($post_parent and !get_post($post_parent)) {
			return ['status' => 0, 'msg' => __('post_parent无效', 'wnd')];
		}

		/**
		 *@since 2019.04.16
		 *上传权限过滤
		 */
		$can_upload_file = apply_filters('wnd_can_upload_file', ['status' => 1, 'msg' => ''], $post_parent, $meta_key);
		if ($can_upload_file['status'] === 0) {
			return $can_upload_file;
		}

		// These files need to be included as dependencies when on the front end.
		if (!is_admin()) {
			require ABSPATH . 'wp-admin/includes/image.php';
			require ABSPATH . 'wp-admin/includes/file.php';
			require ABSPATH . 'wp-admin/includes/media.php';
		}

		/**
		 *@since 2019.05.06 改写
		 *遍历文件上传
		 */
		$return_array = []; // 定义图片信息返回数组
		$files        = $_FILES['wnd_file']; //暂存原始上传信息，后续将重写$_FILES全局变量以适配WordPress上传方式

		foreach ($files['name'] as $key => $value) {
			// 将多文件上传数据遍历循环后，重写为适配 media_handle_upload 的单文件模式
			$file = [
				'name'     => $files['name'][$key],
				'type'     => $files['type'][$key],
				'tmp_name' => $files['tmp_name'][$key],
				'error'    => $files['error'][$key],
				'size'     => $files['size'][$key],
			];
			$_FILES = ['temp_key' => $file];

			// 单文件错误检测
			if ($_FILES['temp_key']['error'] > 0) {
				$return_array[] = ['status' => 0, 'msg' => 'Error: ' . $_FILES['temp_key']['error']];
				continue;
			}

			//上传文件并附属到对应的post parent 默认为0 即孤立文件
			$file_id = media_handle_upload('temp_key', $post_parent);

			// 上传失败
			if (is_wp_error($file_id)) {
				$return_array[] = ['status' => 0, 'msg' => $file_id->get_error_message()];
				continue;
			}

			$url = wp_get_attachment_url($file_id);

			// 判断是否为图片
			if (strrpos($file['type'], 'image') !== false) {
				//根据尺寸进行图片裁剪
				if ($save_width or $save_height) {
					//获取文件服务器路径
					$image_file = get_attached_file($file_id);
					$image      = wp_get_image_editor($image_file);
					if (!is_wp_error($image)) {
						$image->resize($save_width, $save_height, ['center', 'center']);
						$image->save($image_file);
					}
				}

				// 返回缩略图
				$thumbnail = wnd_get_thumbnail_url($url, $thumbnail_width, $thumbnail_height);
			}

			// 将当前上传的图片信息写入数组
			$temp_array = [
				'status' => 1,
				'data'   => [
					'url'       => $url,
					'thumbnail' => $thumbnail ?? 0,
					'id'        => $file_id,
					'post'      => get_post($file_id),
				],
				'msg'    => __('上传成功', 'wnd'),
			];
			$return_array[] = $temp_array;

			/**
			 *@since 2019.02.13 当存在meta key时，表明上传文件为特定用途存储，仅允许上传单个文件
			 *@since 2019.05.05 当meta key == gallery 表示为上传图集相册 允许上传多个文件
			 */
			if ($meta_key != 'gallery') {
				//处理完成根据用途做下一步处理
				do_action('wnd_upload_file', $file_id, $post_parent, $meta_key);
				break;
			}
		}
		unset($key, $value);

		/**
		 *@since 2019.05.05 当meta key == gallery 表示为上传图集相册 允许上传多个文件
		 */
		if ($meta_key == 'gallery') {
			do_action('wnd_upload_gallery', $return_array, $post_parent);
		}

		// 返回上传信息二维数组合集
		return $return_array;
	}
}
