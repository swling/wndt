<?php
use Wndt\Model\Wndt_Config;

/**
 *@since 2020.4.13
 *获取配置选项
 */
function wndt_get_config($config_key) {
	return Wndt_Config::get($config_key);
}

// ############################################################################ 相关terms
function wndt_get_related_terms($current_term_id, $terms_type, $order_by = 'count', $order = 'DESC') {
	global $wpdb;

	$terms = wp_cache_get($current_term_id . $terms_type . $order_by . $order, 'wndt_related_terms');
	if ($terms === false) {
		$terms = $wpdb->get_results
			("
	    	SELECT DISTINCT terms2.term_id as term_id, terms2.name as name
	    	FROM
	    		$wpdb->posts as p1
	    		INNER JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID
	    		INNER JOIN $wpdb->terms as terms1 ON r1.term_taxonomy_id = terms1.term_id,

	    		$wpdb->posts as p2
	    		INNER JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID
	    		INNER JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
	    		INNER JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id
	    	WHERE
	    		terms1.term_id = '$current_term_id' AND p1.post_status = 'publish' AND  t2.taxonomy = '$terms_type' AND p1.ID = p2.ID
	    	ORDER by $order_by $order
	    ");
		wp_cache_set($current_term_id . $terms_type . $order_by . $order, $terms, 'wndt_related_terms', 3600);

	}

	return $terms;
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

//  远程发送 POST数据  since 2018.9.21
function wndt_send_post($url, $post_data) {
	$postdata = http_build_query($post_data);
	$options  = [
		'http' => [
			'method'  => 'POST',
			'header'  => 'Content-type:application/x-www-form-urlencoded',
			'content' => $postdata,
			'timeout' => 15 * 60, // 超时时间（单位:s）
		],
	];
	$context = stream_context_create($options);
	$result  = file_get_contents($url, false, $context);
	return $result;
}

/**
 *获取自定义类型排序
 */
function wndt_get_post_type() {

	// 需要对顺序进行自定义
	$enable_company = wndt_get_config('enable_company');
	$enable_people  = wndt_get_config('enable_people');

	if ($enable_company) {
		$post_types['company'] = 'company';
	} elseif ($enable_people) {
		$post_types['people'] = 'people';
	}

	$post_types['supply'] = 'supply';
	$post_types['demand'] = 'demand';

	if ($enable_company and $enable_people) {
		$post_types['people'] = 'people';
	}

	// 获取所有公开的，有存档的自定义类型
	$all_post_types = get_post_types(
		[
			'public'      => true,
			'show_ui'     => true,
			'_builtin'    => false,
			'has_archive' => true,
		],
		'names',
		'and'
	);

	// 合并数组、保持顺序
	return array_merge($post_types, $all_post_types);
}

/**
 *@since 2019.10.10
 *logo副标题
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
