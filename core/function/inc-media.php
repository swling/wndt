<?php
/**
 *@since 初始化
 *下载文件
 *通过php脚本的方式将文件发送到浏览器下载，避免保留文件的真实路径
 *然而，用户仍然可能通过文件名和网站结构，猜测到可能的真实路径，
 *因此建议将$file定义在网站目录之外，这样通过任何url都无法访问到文件存储目录
 *主要用户付费下载
 *@param string		$the_file 	本地或远程完整文件地址
 *@param string 	$rename 	发送给浏览器的文件名称，重命名后可防止在收费类下载场景中，用户通过文件名猜测路径
 */
function wnd_download_file($the_file, $rename = 'download') {
	// 获取文件后缀信息
	$ext = '.' . pathinfo($the_file)['extension'];

	// Force download
	header('Content-type: application/x-file-to-save');
	header('Content-Disposition: attachment; filename=' . get_option('blogname') . '-' . $rename . $ext);
	ob_end_clean();
	readfile($the_file);
	exit;
}

/**
 *@since 2019.01.22
 *保存文章中的外链图片，并替换html图片地址
 *@param 	string 	 $content
 *@param 	string 	 $upload_dir
 *@param 	int 	 $post_id
 *
 *@return 	string 	$content 	经过本地化后的内容
 */
function wnd_download_remote_images($content, $upload_dir, $post_id) {
	if (empty($content)) {
		return;
	}

	$preg = preg_match_all('/<img.*?src="(.*?)"/', stripslashes($content), $matches);
	if ($preg) {
		$i = 1;
		foreach ($matches[1] as $image_url) {
			if (empty($image_url)) {
				continue;
			}

			$pos = strpos($image_url, $upload_dir); // 判断图片链接是否为外链
			if ($pos === false) {
				$local_url = wnd_download_remote_image($image_url, $post_id, time() . '-' . $i);
				if (!is_wp_error($local_url)) {
					$content = str_replace($image_url, $local_url, $content);
				}
			}
			$i++;
		}
		unset($image_url);
	}

	return $content;
}

/**
 *@since 2019.01.22
 *WordPress 远程下载图片 并返回上传后的图片地址/html 或 id
 *
 *@param string 	$url 			远程URL
 *@param int 		$post_parent 	需要附属到的Post ID
 *@param string 	$title 			文件名称
 *@param string 	$return  		Optional. Accepts 'html' (image tag html) or 'src' (URL), or 'id' (attachment ID). Default 'html'.
 *
 *@return string|WP_Error Populated HTML img tag on success, WP_Error object otherwise.
 */
function wnd_download_remote_image($url, $post_parent, $title, $return = 'src') {
	if (!function_exists('media_sideload_image')) {
		require ABSPATH . 'wp-admin/includes/media.php';
		require ABSPATH . 'wp-admin/includes/file.php';
		require ABSPATH . 'wp-admin/includes/image.php';
	}
	return media_sideload_image($url, $post_parent, $title, $return);
}

/**
 *@since 2019.05.08 获取图像缩略图
 *需要将图像存储在阿里云oss，并利用filter对wp_get_attachment_url重写为阿里oss地址
 *阿里云的图片处理
 *@link https://help.aliyun.com/document_detail/44688.html
 *截至2019.05.11图片处理定价：每月0-10TB：免费 >10TB：0.025元/GB
 *
 *@param int|string 	$is_or_url 	 	附件post id 或者oss完整图片地址
 *@param int 			$width 	 		图片宽度
 *@param int 			$height 		图片高度
 */
function wnd_get_thumbnail_url($id_or_url, $width = 160, $height = 120) {
	$url = is_numeric($id_or_url) ? wp_get_attachment_url($id_or_url) : $id_or_url;
	if (!$url) {
		return false;
	}

	return $url . '?x-oss-process=image/resize,m_fill,w_' . $width . ',h_' . $height;
}
