<?php
namespace Wnd\Module;

/**
 *@since 2019.02.15
 *获取文章信息
 */
class Wnd_Post_Info extends Wnd_Module {

	public static function build($post_id = 0) {
		$post = $post_id ? get_post($post_id) : false;
		if (!$post) {
			return __('ID无效', 'wnd');
		}

		// 站内信阅读后，更新为已读 @since 2019.02.25
		if ('mail' == $post->post_type and $post->post_type != 'private') {
			wp_update_post(['ID' => $post->ID, 'post_status' => 'private']);
		}

		if (wnd_get_post_price($post->ID)) {
			return static::build_message(__('付费文章不支持预览', 'wnd'));
		}

		$html = '<article>';
		$html .= $post->post_content;
		$html .= '</article>';
		return $html;
	}
}
