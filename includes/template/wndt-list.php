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
		$method = 'build_' . ($simple ? $post->post_type . '_list_simple' : $post->post_type . '_list_thumbnail');
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
		$html = '<div class="post-list columns is-multiline is-tablet company-list-simple box">';
		$html .= '<div class="column is-full is-marginless is-paddingless">';
		$html .= '<h3><a href="' . get_permalink($post) . '">' . $post->post_title . '</a></h3>';
		$html .= '</div>';

		$html .= '<div class="column is-narrow is-marginless">';
		$html .= wndt_post_thumbnail($post->ID, '200', '150');
		$html .= '</div>';

		$html .= '<div class="column is-hidden-mobile">' . wp_trim_words($post->post_excerpt ?: $post->post_content, 160) . '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 *@since 公司列表
	 */
	protected static function build_post_list_thumbnail($post) {

		// 如为管理员添加，则显示post_title，反之为注册用户，显示为用户display_name
		// $display_name = wnd_is_manager($post->post_author) ? $post->post_title : get_user_by('ID', $post->post_author)->display_name;

		$html = '<div class="post-list columns is-multiline is-tablet is-size-7-mobile box">';

		$html .= '<div class="column is-narrow is-hidden-mobile">';
		$html .= wndt_post_thumbnail($post->ID, '100', '100');
		$html .= '</div>';

		$html .= '<div class="column">';
		$html .= '<h3 class="is-size-6-mobile"><a href="' . get_permalink($post) . '">' . $post->post_title . '</a>';
		$html .= '</h3>';

		$html .= '<div class="category">';
		$html .= '&nbsp;' . get_the_term_list($post->ID, 'category');
		$html .= '</div>';

		$html .= get_the_term_list(
			$post->ID,
			$post->post_type . '_tag',
			'<div class="tags"><span class="icon"><i class="fas fa-hashtag"></i></span>',
			'<span class="icon"><i class="fas fa-hashtag"></i></span>',
			'</div>'
		);

		$html .= '<div class="excerpt content">';
		$html .= wp_trim_words($post->post_excerpt ?: $post->post_content, 100);
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="column is-narrow has-text-centered">';
		$html .= '<p class="is-size-7">' . get_the_time('m-d H:i', $post) . '</p>';
		// $html .= wnd_modal_button('免费咨询', 'wndt_contact_info', $post->ID, 'is-outlined is-small');
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
