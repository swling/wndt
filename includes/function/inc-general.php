<?php
/**
 * 获取配置选项
 * @since 2020.4.13
 */
function wndt_get_config($config_key) {
	return Wndt\Utility\Wndt_Config::get($config_key);
}

//############################################################################ 根据用户id和文章类型统计文章数量 #已发布 #待审 #草稿
function wndt_get_post_count($user_id, $post_type) {
	$args = [
		'author'        => $user_id,
		'post_type'     => $post_type,
		'no_found_rows' => 0,
		'post_status'   => ['publish', 'pending', 'draft'],
	];

	$posts = new WP_Query($args);
	return $posts->found_posts;
	wp_reset_postdata();
}

/**
 * logo副标题
 * @since 2019.10.10
 */
function wndt_get_sub_title($sep = '') {
	if (is_singular('post')) {
		return $sep . '文章详情';
	}

	if (is_singular('page')) {
		return $sep . get_the_title();
	}

	if (is_singular()) {
		return $sep . get_post_type_object(get_query_var('post_type'))->label . '详情';
	}

	if (is_tax() or is_tag() or is_category()) {
		return $sep . get_queried_object()->name;
	}

	if (is_post_type_archive()) {
		return $sep . get_queried_object()->label;
	}

	if (is_search()) {
		return $sep . '搜索';
	}

	return false;
}

/**
 * 获取用户 vip 有效期时间戳
 */
function wndt_get_vip_timestamp(int $user_id): int {
	return (int) (wnd_get_user_meta($user_id, 'vip') ?: 0);
}
