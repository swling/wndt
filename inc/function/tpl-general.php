<?php

/**
 *@since 2019.04.26
 *文章列表
 */
function wndt_post_list_tpl($post, $simple = false) {
	return Wnd\Template\Wnd_List::build($post, $simple);
}

/**
 *商户认证标识
 *@since 2019.05.25
 **/
function wndt_cert_icon($user_id, $space = '&nbsp;') {
	if (wndt_is_cert($user_id)) {
		return $space . '<span class="icon"><i class="fab fa-vimeo-square"></i></span>';
	} else {
		return null;
	}
}

/**
 *@since 2019.06.05
 *名片profile
 **/
function wndt_people_profile($post_id, $avatar_size = 200, $with_contact = true) {
	$post = get_post($post_id);
	if (!$post) {
		return;
	}

	$html = '<div class="people-profile content">';
	$html .= wndt_post_thumbnail($post->ID, $avatar_size, $avatar_size);

	$html .= '<p class="is-size-7"><a href="' . get_permalink($post) . '">' . $post->post_title . '</a>';
	$html .= wndt_cert_icon($post->post_author);
	$html .= '</p>';
	if ($with_contact) {
		$html .= '<a class="is-' . wnd_get_config('primary_color') . ' is-small button" onclick="wnd_ajax_modal(\'wndt_contact_info\',\'' . $post->ID . '\')">联系我</a>&nbsp';
	}
	$html .= '</div>';

	return $html;
}

/**
 *@since 2019.07.22
 */
function wndt_list_bookmarks($args = []) {
	$categories = get_terms(['taxonomy' => 'link_category']);

	$html = '<div class="columns bookmarks content">';
	foreach ($categories as $category) {
		$bookmarks = get_bookmarks(['category' => $category->term_id]);
		if (!$bookmarks) {
			return '<ul class="column is-narrow"><li>没有链接</li></ul>';
		}

		$html .= '<ul class="column is-narrow">';
		$html .= '<h3>' . $category->name . '</h3>';
		foreach ($bookmarks as $bookmark) {
			$target = $bookmark->link_target ? 'target="' . $bookmark->link_target . '"' : 'target="_blank"';
			$html .= '<li id="bookmark-' . $bookmark->link_id . '">';
			$html .= '<a href="' . $bookmark->link_url . '" ' . $target . '>' . $bookmark->link_name . '</a>';
			$html .= '</li>';
		}
		unset($bookmark);
		$html .= '</ul>';
	}
	unset($category);
	$html .= '</div>';

	return $html;
}

/**
 *
 *@since 2019.09.18
 *类型导航
 */
function wndt_post_type_nav_items() {

	$html = '';
	foreach (wndt_get_post_type() as $post_type) {
		switch ($post_type) {
		case 'company':
			$icon = '<span class="icon"><i class="fa fa-building"></i></span>';
			break;
		case 'supply':
			$icon = '<span class="icon"><i class="fa fa-bullhorn"></i></span>';
			break;

		case 'demand':
			$icon = '<span class="icon"><i class="fa fa-file-alt"></i></span>';
			break;

		case 'people':
			$icon = '<span class="icon"><i class="fa fa-address-card"></i></span>';
			break;

		default:
			$icon = '';
			break;
		}

		$class = (is_post_type_archive($post_type) or is_singular($post_type)) ? 'navbar-item is-active' : 'navbar-item';
		$html .= '<a class="' . $class . '" href="' . get_post_type_archive_link($post_type) . '">';
		$html .= $icon . '&nbsp;';
		$html .= get_post_type_object($post_type)->label;
		$html .= '</a>';
	}
	unset($post_type);

	return $html;

	$args = [
		'public'      => true,
		'has_archive' => true,
		'show_ui'     => true,
		'_builtin'    => false,
	];

	$post_types = get_post_types($args, $output = 'object', $operator = 'and');

	foreach ($post_types as $post_type) {
		echo '<a class="navbar-item" href="' . get_post_type_archive_link($post_type) . '">';
		echo $post_type->label;
		echo '</a>';
	}
}

/**
 *@since 2019.04.16 获取文章头像
 *优先获取文章缩略图，其次获取文章作者头像，两者均无则返回false
 */
function wndt_post_thumbnail($post_id, $width, $height) {

	// 文章缩略图
	if (wnd_get_post_meta($post_id, '_thumbnail_id')) {
		$post_thumbnail = wnd_post_thumbnail($post_id, $width, $height);
		return '<a href ="' . get_permalink($post_id) . '">' . $post_thumbnail . '</a>';
	}

	// 文章作者头像
	$author_id  = get_post($post_id)->post_author;
	$avatar_id  = wnd_get_user_meta($author_id, 'avatar');
	$avatar_url = wnd_get_user_meta($author_id, 'avatar_url');
	if ($avatar_id or $avatar_url) {
		$img_src = $avatar_id ? wnd_get_thumbnail_url($avatar_id, $width, $height) : $avatar_url;
		return '<a href ="' . get_permalink($post_id) . '"><img class="thumbnail" src="' . $img_src . '" width="' . $width . '" height="' . $height . '"  ></a>';
	}

	// 默认用户头像
	return '<a href ="' . get_permalink($post_id) . '"><img class="thumbnail" src="' . wnd_get_config('default_avatar_url') . '" width="' . $width . '" height="' . $height . '"  ></a>';
}

/**
 *@since 2019.10.11
 *自定义侧边栏导航
 */
function wndt_get_post_type_menu() {

	$html = '<ul class="menu-list">';
	$html .= '<li>导航</li>';
	$html .= '<li>';
	$html .= '<ul>';
	foreach (wndt_get_post_type() as $post_type) {
		switch ($post_type) {
		case 'company':
			$icon = '<span class="icon"><i class="fa fa-building"></i></span>';
			break;
		case 'supply':
			$icon = '<span class="icon"><i class="fa fa-bullhorn"></i></span>';
			break;

		case 'demand':
			$icon = '<span class="icon"><i class="fa fa-file-alt"></i></span>';
			break;

		case 'people':
			$icon = '<span class="icon"><i class="fa fa-address-card"></i></span>';
			break;

		default:
			$icon = '';
			break;
		}

		$class = (is_post_type_archive($post_type) or is_singular($post_type)) ? ' is-active' : '';
		$html .= '<li><a class="' . $class . '" href="' . get_post_type_archive_link($post_type) . '">';
		$html .= $icon . '&nbsp;';
		$html .= get_post_type_object($post_type)->label;
		$html .= '</a></li>';
	}
	unset($post_type);
	$html .= '</ul>';
	$html .= '</li>';
	$html .= '</ul>';

	return $html;
}

/**
 *@since 2019.10.11
 *自定义类型顶部Tabs
 */
function get_post_type_tabs() {
	$html = '<div class="tabs column is-marginless post-type-tabs">';
	$html .= '<ul>';
	foreach (wndt_get_post_type() as $post_type) {
		switch ($post_type) {
		case 'company':
			$icon = '<span class="icon"><i class="fa fa-building"></i></span>';
			break;
		case 'supply':
			$icon = '<span class="icon"><i class="fa fa-bullhorn"></i></span>';
			break;

		case 'demand':
			$icon = '<span class="icon"><i class="fa fa-file-alt"></i></span>';
			break;

		case 'people':
			$icon = '<span class="icon"><i class="fa fa-address-card"></i></span>';
			break;

		default:
			$icon = '';
			break;
		}

		$class = (is_post_type_archive($post_type) or is_singular($post_type)) ? ' is-active' : '';
		$html .= '<li class="' . $class . '">';
		$html .= '<a href="' . get_post_type_archive_link($post_type) . '">';
		$html .= $icon . '&nbsp;';
		$html .= get_post_type_object($post_type)->label;
		$html .= '</a>';
		$html .= '</li>';
	}
	unset($post_type);
	$html .= '</ul>';
	$html .= '</div>';

	return $html;
}

/**
 *
 *@since 2019.09.18
 *类型导航
 */
function wndt_category_nav_items($args = []) {
	$defaults = ['taxonomy' => 'category', 'orderby' => 'count'];
	$args     = wp_parse_args($args, $defaults);
	$terms    = get_terms($args);
	$taxonomy = $args['taxonomy'];

	$html = '';
	foreach ($terms as $term) {
		$class = (is_tax($taxonomy, $term->term_id) or has_term($term->term_id, $taxonomy)) ? 'navbar-item is-active' : 'navbar-item';
		$html .= '<a class="' . $class . '" href="' . get_term_link($term->term_id) . '">';
		$html .= $term->name;
		$html .= '</a>';
	}
	unset($term);

	return $html;
}