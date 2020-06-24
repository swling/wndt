<?php
/**
 *Description: 基于WndWP构建的黄页招投标插件
 *Version: 0.1
 *Author: swling
 *Author URI: https://wndwp.com
 *@since 2019.1.16 从原adbid主题中抽离整合为插件
 *@since 2019.09重新将插件整合到主题：wnd-biz
 */

if (!defined('WND_VER') and !is_admin()) {
	exit('本主题依赖wnd-frontend插件');
}

// 本地不显示错误奇怪故补充之
if (WP_DEBUG) {
	ini_set('display_errors', 'On');
}

// 定义当前主题 外网 url 取代 get_template_directory_uri ，因其会导致 options 不断自增：_site_transient_theme_roots
define('WNDT_URL', get_option('home') . '/wp-content/themes/' . basename(dirname(__FILE__)));

$theme_ver = 0.03;

/**
 *加载php模块
 */
require TEMPLATEPATH . '/inc/wndt-load.php';

// ###########################################################
// 加载自定义js 并引入 wp-ajax.php处理脚本
function wndt_site_scripts() {
	global $theme_ver;

	if (!is_admin()) {
		// 替换jQuery为公共cdn库，省点流量吧
		wp_deregister_script('jquery');
		wp_register_script('jquery', ('https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js'), false, null);

		// wp5.0+ block css
		wp_deregister_style('wp-block-library');
	}

	//################################### 加载……
	wp_enqueue_script('wndt_functions', $src = WNDT_URL . '/static/js/functions.min.js', $deps = ['jquery'], $theme_ver, $in_footer = false);
	if (is_singular() and comments_open() and get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}

	// wp_enqueue_style('bulma-extensions', '//cdn.jsdelivr.net/npm/bulma-extensions@6.2.4/dist/css/bulma-extensions.min.css');
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
			$title['title'] .= ' - ' . get_term($value)->name;
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

	// 默认排序
	// if (!isset($_GET['orderby'])) {
	//     $query->set('orderby', 'modified');
	// }

	// 指定搜索类型
	if ($query->is_search()) {
		if (isset($_GET['post_type'])) {
			if ($_GET['post_type'] != 'all') {
				$query->set('post_type', $_GET['post_type']);
			}

		}
	}

}

// ################################# 移除规范链接 新增手动添加：page页面启用了动态查询 since 2018.10.25
remove_action('wp_head', 'rel_canonical');
function wndt_rel_canonical() {

	if (is_single()) {
		echo '<link rel="canonical" href="' . get_the_permalink() . '"/>' . PHP_EOL;
	}

}
add_action('wp_head', 'wndt_rel_canonical');
