<?php
namespace Wndt\Module;

/**
 *@since 2020.04.27
 *
 *未登录用户中心
 *
 */
class Wndt_User_Center_Login extends Wndt_User_Center {

	public static function build() {
		$html = '<div id="user-center">';
		$html .= wnd_ajax_embed('wnd_user_center', ['do' => 'login']);
		$html .= '</div>';
		return $html;
	}
}