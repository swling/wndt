<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module;

/**
 *列表模板
 */
class Wndt_Home extends Wnd_Module {

	public static function build() {
		return '';
	}
	/**
	 *构建列表输出模板
	 *
	 */

	public static function list() {
		$html  = '';
		$terms = get_terms($args = array('taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC'));

		// 循环输出分类
		foreach ($terms as $term) {
			$posts = get_posts(array('cat' => $term->term_id, 'post_type' => 'post'));
			$html .= '<div class="column is-4">';
			$html .= '<div class="box">';
			$html .= '<h3 class="is-size-5"><a href="' . get_term_link($term) . '">' . $term->name . '</a></h3>';
			$html .= '<ul>';
			/**
			 *@循环输出文章列表
			 */
			foreach ($posts as $post) {
				$html .= '<li class="col-lg-3 col-md-3 col-sm-4 col-xs-6">';
				$html .= '<a class="navbar-item" href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>';
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
			$html .= '</div>';
			unset($term);
		}
		return $html;
	}

}