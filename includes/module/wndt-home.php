<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module_Html;

/**
 *列表模板
 */
class Wndt_Home extends Wnd_Module_Html {

	protected static function build($args = []): string {
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
			$html .= '<div class="home-list column is-4">';
			$html .= '<div class="box">';
			$html .= '<h3 class="is-size-5"><a href="' . get_term_link($term) . '">' . $term->name . '</a></h3>';
			$html .= '<ul>';
			/**
			 *@循环输出文章列表
			 */
			foreach ($posts as $post) {
				$html .= '<li class="mb-2 mt-1">';
				$html .= '<a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>';
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
