<?php
namespace Wndt\Template;

/**
 *列表模板
 */
class Wndt_List {

	/**
	 *构建列表输出模板
	 *
	 */
	public static function build(object $post, $simple = false) {
		$method = 'build_' . ($simple ? $post->post_type . '_list_simple' : $post->post_type . '_list');
		if (method_exists(__CLASS__, $method)) {
			return static::$method($post);
		} else {
			return static::build_post_list($post);
		}
	}

	/**
	 *@since 2019.04.26
	 *文章列表
	 *根据文章类型自动匹配列表函数：'wndt_' . $post->post_type . '_list';
	 */
	protected static function build_post_list($post) {
		$html = '<div class="post-list columns is-multiline is-tablet company-list-simple">';
		$html .= '<div class="column is-full is-marginless is-paddingless">';
		$html .= '<h3><a href="' . get_permalink($post) . '">' . $post->post_title . '</a></h3>';
		$html .= '</div>';

		$html .= '<div class="column is-narrow is-marginless">';
		$html .= wndt_post_thumbnail($post->ID, '100', '100');
		$html .= '</div>';

		$html .= '<div class="column is-hidden-mobile">' . wp_trim_words($post->post_excerpt ?: $post->post_content, 160) . '</div>';
		$html .= '</div>';

		return $html;
	}
}
