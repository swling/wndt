<?php
namespace Wndt\Module;

/**
 *@since 2020.05.08
 *
 *搜索框
 *
 */
class Wndt_Search_Form {

	public static function build() {
		$html = '<form role="search" method="get" id="searchform" action="' . home_url() . '">';
		$html .= '<div class="field has-addons has-addons-right">';
		$html .= '<p class="control">';
		$html .= '<span class="select">';
		$html .= '<select name="post_type">';
		$html .= ' <option value="all"> - 全站 - </option>';
		foreach (get_post_types(['public' => true, 'has_archive' => true], 'object', 'and') as $post_type) {
			$html .= '<option value="' . $post_type->name . '">' . $post_type->label . '</option>';
		}
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</p>';
		$html .= '<p class="control is-expanded">';
		$html .= '<input class="input" type="text" name="s" placeholder="搜索关键词" required="required">';
		$html .= '</p>';
		$html .= '<p class="control">';
		$html .= '<input type="submit" class="button is-danger" value="搜索" />';
		$html .= '</p>';
		$html .= '</div>';
		$html .= '</form>';

		return $html;
	}
}
