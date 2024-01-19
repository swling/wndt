<?php
/**
 *Description: 基于WndWP构建的黄页招投标插件
 *Version: 0.1
 *Author: swling
 *Author URI: https://wndwp.com
 *@since 2019.1.16 从原adbid主题中抽离整合为插件
 *@since 2019.09重新将插件整合到主题：wnd-biz
 */

if (!defined('WND_VER')) {
	if (is_admin()) {
		return false;
	} else {
		exit('本主题依赖wnd-frontend插件');
	}
}

// 本地不显示错误奇怪故补充之
if (is_super_admin() and !is_admin()) {
	ini_set('display_errors', 'On');
}

// 定义当前主题路径（替换被 WP6.4 废弃的 TEMPLATEPATH）
define('WNDT_PATH', get_template_directory());

// 定义当前主题 外网 url 取代 get_template_directory_uri ，因其会导致 options 不断自增：_site_transient_theme_roots
define('WNDT_URL', get_option('home') . '/wp-content/themes/' . basename(dirname(__FILE__)));

// 从style.css读取版本号
$theme_ver = wp_get_theme(basename(dirname(__FILE__)))->get('Version');

// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
// set_site_transient('update_themes', null);

/**
 *加载php模块
 */
// require TEMPLATEPATH . '/includes/wndt-load.php';
Wndt\Model\Wndt_Init::get_instance();

// ###########################################################
// 加载自定义js 并引入 wp-ajax.php处理脚本
function wndt_site_scripts() {
	global $theme_ver;

	if (!is_admin()) {
		// wp5.0+ block css
		wp_deregister_style('wp-block-library');
	}

	//################################### 加载……
	wp_enqueue_style('style', WNDT_URL . '/style.css', [], $theme_ver);

	// 代码高亮
	if (is_singular()) {
		wp_enqueue_script('prism-script', WNDT_URL . '/static/prism/prism.js', $theme_ver);
		wp_enqueue_style('prism-style', WNDT_URL . '/static/prism/prism.css', $theme_ver);
	}
}

add_action('wp_enqueue_scripts', 'wndt_site_scripts');

// ################################# 定义 seo title
function wndt_seo_title($title) {
	// 自定义分类 标题SEO重写
	if (is_tax()) {
		$post_type       = explode('_', get_queried_object()->taxonomy)[0];
		$post_type_label = wndt_get_config('' . $post_type . '_label') ?: '';
		$title['title'] .= $post_type_label ? ' - ' . $post_type_label : '';
	}

	if (empty($_GET)) {
		return $title;
	}

	foreach ($_GET as $key => $value) {
		if (strpos($key, '_term_') === 0) {
			$term = get_term($value);
			if ($term and !is_wp_error($term)) {
				$title['title'] .= ' - ' . $term->name;
			}
			continue;
		}

		if (strpos($key, 'type') === 0) {
			$post_type_object = get_post_type_object($value);
			$title['title'] .= $post_type_object ? ' - ' . $post_type_object->label : '';
			continue;
		}
	}

	return $title;
}
add_filter('document_title_parts', 'wndt_seo_title');

// pre get posts #######################################
add_action('pre_get_posts', 'wndt_post_filter');
function wndt_post_filter($query) {
	if (is_admin() or !$query->is_main_query()) {
		return $query;
	}

	// 禁止查询 SQL_CALC_FOUND_ROWS
	$query->set('no_found_rows', true);
}

// ################################# 移除规范链接 新增手动添加：page页面启用了动态查询 since 2018.10.25
remove_action('wp_head', 'rel_canonical');
function wndt_rel_canonical() {
	if (is_single()) {
		echo '<link rel="canonical" href="' . get_the_permalink() . '"/>' . PHP_EOL;
	}
}
add_action('wp_head', 'wndt_rel_canonical');

########################################

add_action('init', 'Wndt\Model\Wndt_Keys::hook');
