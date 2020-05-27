<?php

/**
 *@since 2019.12.31
 *
 *Post List 表格列表
 *
 *@param WP_Query 实例化
 */
function wnd_list_table(WP_Query $query) {
	return Wnd\Template\Wnd_List_Table::build($query);
}

/**
 *@since 2019.05.23
 *面包屑导航
 **/
function wnd_breadcrumb($font_size = 'is-small', $hierarchical = true) {
	if (is_home() or is_author()) {
		return;
	}

	/**
	 *columns
	 **/
	$html = '<div class="breadcrumb-wrap columns is-mobile">';

	/**
	 *左侧导航
	 **/
	$html .= '<div class="column">';
	$html .= '<nav class="breadcrumb ' . $font_size . '" aria-label="breadcrumbs">';
	$html .= '<ul>';
	$html .= '<li><a href="' . home_url() . '">' . __('首页', 'wnd') . '</a></li>';
	$queried_object = get_queried_object();

	// 内容页
	if (is_single()) {
		$html .= '<li><a href="' . get_post_type_archive_link($queried_object->post_type) . '">' . get_post_type_object($queried_object->post_type)->label . '</a></li>';

		$taxonomies = get_object_taxonomies($queried_object->post_type, $output = 'object');
		if ($taxonomies) {
			// 如果存在父级则调用父级的分类信息
			$post_id = $queried_object->post_parent ?: $queried_object->ID;

			foreach ($taxonomies as $taxonomy) {
				if ($hierarchical and !is_taxonomy_hierarchical($taxonomy->name) or !$taxonomy->public) {
					continue;
				}

				$html .= get_the_term_list($queried_object->ID, $taxonomy->name, '<li>', '', '</li>');
			}
			unset($taxonomy);
		}

		// 父级post
		if ($queried_object->post_parent) {
			$html .= '<li><a href="' . get_permalink($queried_object->post_parent) . '">' . get_the_title($queried_object->post_parent) . '</a></li>';
		}

		//页面
	} elseif (is_page()) {

		// 父级page
		if ($queried_object->post_parent) {
			$html .= '<li><a href="' . get_permalink($queried_object->post_parent) . '">' . get_the_title($queried_object->post_parent) . '</a></li>';
		}

		$html .= '<li class="is-active"><a>' . get_the_title() . '</a></li>';

		//post类型归档
	} elseif (is_post_type_archive()) {
		$html .= '<li class="is-active"><a>' . $queried_object->label . '</a></li>';

		//其他归档页
	} elseif (is_archive()) {
		$args = http_build_query(['taxonomy' => $queried_object->taxonomy, 'orderby' => 'name']);
		$html .= '<li><a onclick="wnd_ajax_modal(\'wnd_terms_list\',\'' . $args . '\')">' . get_taxonomy($queried_object->taxonomy)->label . '</a></li>';
		$html .= '<li class="is-active"><a>' . $queried_object->name . '</a></li>';

	} else {
		$html .= '<li class="is-active"><a>' . wp_title('', false) . '</a></li>';
	}

	$html .= '</ul>';
	$html .= '</nav>';
	$html .= '</div>';

	/**
	 *左侧导航
	 **/
	$html .= '<div class="column is-narrow is-size-7 breadcrumb-right">';
	$breadcrumb_right = null;
	// 内页编辑
	if (is_single()) {
		if (current_user_can('edit_post', $queried_object->ID)) {
			$breadcrumb_right .= '<a href="' . get_edit_post_link($queried_object->ID) . '">[' . __('编辑', 'wnd') . ']</a>';
			$breadcrumb_right .= '&nbsp;<a onclick="wnd_ajax_modal(\'wnd_post_status_form\',\'' . $queried_object->ID . '\')">[' . __('状态', 'wnd') . ']</a>';
		}
	}
	$html .= apply_filters('wnd_breadcrumb_right', $breadcrumb_right);
	$html .= '</div>';

	/**
	 *容器结束
	 **/
	$html .= '</div>';

	return $html;
}

/**
 *@since 2019.05.26 bulma 颜色下拉选择
 */
function wnd_dropdown_colors($name, $selected) {
	$colors = [
		'primary',
		'success',
		'info',
		'link',
		'warning',
		'danger',
		'dark',
		'black',
		'light',
	];

	$html = '<select name="' . $name . '">';
	foreach ($colors as $color) {
		if ($selected == $color) {
			$html .= '<option selected="selected" value="' . $color . '">' . $color . '</option>';
		} else {
			$html .= '<option value="' . $color . '">' . $color . '</option>';
		}
	}
	unset($color);
	$html .= '</select>';

	return $html;
}

/**
 *@since 2019.07.16
 *创建订单链接
 *@param int $post_id 产品/文章ID
 */
function wnd_order_link($post_id) {
	return wnd_get_do_url() . '?action=payment&post_id=' . $post_id . '&_wpnonce=' . wp_create_nonce('payment');
}

/**
 *@since 2019.05.05
 *gallery 相册展示
 *@param $post_id 			int 		相册所附属的文章ID，若为0，则查询当前用户字段
 *@param $thumbnail_width 	number 		缩略图宽度
 *@param $thumbnail_height 	number 		缩略图高度
 **/
function wnd_gallery($post_id, $thumbnail_width = 160, $thumbnail_height = 120) {
	$images = $post_id ? wnd_get_post_meta($post_id, 'gallery') : wnd_get_user_meta(get_current_user_id(), 'gallery');
	if (!$images) {
		return false;
	}

	// 遍历输出图片集
	$html = '<div class="gallery columns is-vcentered is-multiline has-text-centered">';
	foreach ($images as $key => $attachment_id) {
		$attachment_url = wp_get_attachment_url($attachment_id);
		$thumbnail_url  = wnd_get_thumbnail_url($attachment_url, $thumbnail_width, $thumbnail_height);
		if (!$attachment_url) {
			unset($images[$key]); // 在字段数据中取消已经被删除的图片
			continue;
		}

		$html .= '<div class="attachment-' . $attachment_id . '" class="column is-narrow">';
		$html .= '<a><img class="thumbnail" src="' . $thumbnail_url . '" data-url="' . $attachment_url . '"height="' . $thumbnail_height . '" width="' . $thumbnail_width . '"></a>';
		$html .= '</div>';
	}
	unset($key, $attachment_id);
	wnd_update_post_meta($post_id, 'gallery', $images); // 若字段中存在被删除的图片数据，此处更新
	$html .= '</div>';

	return $html;
}

/**
 *@since 2019.02.27 获取WndWP文章缩略图
 *@param int $post_id 	文章ID
 *@param int $width 	缩略图宽度
 *@param int $height 	缩略图高度
 */
function wnd_post_thumbnail($post_id, $width, $height) {
	$post_id = $post_id ?: get_the_ID();
	if ($post_id) {
		$image_id = wnd_get_post_meta($post_id, '_thumbnail_id');
	}

	$url  = $image_id ? wnd_get_thumbnail_url($image_id, $width, $height) : WND_URL . '/static/images/default.jpg';
	$html = '<img class="thumbnail" src="' . $url . '" width="' . $width . '" height="' . $height . '">';

	return apply_filters('wnd_post_thumbnail', $html, $post_id, $width, $height);
}

/**
 *@since 2020.03.21
 *
 *付费阅读按钮
 */
function wnd_paid_reading_button($post_id) {
	return Wnd\Template\Wnd_Pay_Button::build_paid_reading_button($post_id);
}

/**
 *@since 2020.03.21
 *
 *付费下载按钮
 */
function wnd_paid_download_button($post_id) {
	return Wnd\Template\Wnd_Pay_Button::build_paid_download_button($post_id);
}

/**
 *构建消息
 *@since 2020.03.22
 */
function wnd_message($message, $color = '', $centered = false) {
	$class = 'message content wnd-message';
	$class .= $color ? ' ' . $color : '';
	$class .= $centered ? ' has-text-centered' : '';

	return '<div class="' . $class . '"><div class="message-body">' . $message . '</div></div>';
}

/**
 *构建系统通知
 *@since 2020.04.23
 */
function wnd_notification($notification, $add_class = '', $delete = false) {
	$class = 'notification is-light';
	$class .= $add_class ? ' ' . $add_class : '';

	$html = '<div class="' . $class . '">';
	$html .= $delete ? '<button class="delete"></button>' : '';
	$html .= $notification;
	$html .= '</div>';

	return $html;
}

/**
 *呼出弹窗按钮
 *@since 2020.04.23
 *@param $text 		按钮文字
 *@param $event 	点击弹窗
 *@param $param  	传输参数
 *@param $add_calss class
 */
function wnd_modal_button($text, $event = '', $param = '', $add_class = '') {
	$class = 'button';
	$class .= $add_class ? ' ' . $add_class : '';

	$html = '<button class="' . $class . '"';
	$html .= $event ? ' onclick="wnd_ajax_modal(\'' . $event . '\',\'' . $param . '\')"' : '';
	$html .= '>' . $text . '</button>';

	return $html;
}
