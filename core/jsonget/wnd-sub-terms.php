<?php
namespace Wnd\JsonGet;

use Wnd\Model\Wnd_Term;

/**
 *@since 2020.04.14
 *列出term下拉选项
 **/
class Wnd_Sub_Terms extends Wnd_JsonGet {

	public static function get($args = []) {
		$defaults = [
			'taxonomy'   => 'category',
			'parent'     => 0,
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		];
		$args  = wp_parse_args($args, $defaults);
		$terms = Wnd_Term::get_terms_data($args);
		if (!$terms) {
			return '';
		}

		$tips = $args['tips'];
		$html = '';

		$html .= '<option value="-1">- ' . $args['tips'] . ' -</option>';
		foreach ($terms as $key => $value) {
			$html .= '<option value="' . $value . '">' . $key . '</option>';
		}
		unset($term);

		return $html;
	}
}
