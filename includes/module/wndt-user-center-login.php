<?php
namespace Wndt\Module;

/**
 *@since 2020.04.27
 *
 *未登录用户中心
 *
 */
class Wndt_User_Center_Login extends Wndt_User_Center {

	protected static function build(): string {
		$html = '<div id="user-center">';
		$html .= wnd_ajax_embed('wnd_user_center', ['do' => $_GET['do'] ?? 'register']);
		$html .= '</div>';
		return $html;
	}
}
