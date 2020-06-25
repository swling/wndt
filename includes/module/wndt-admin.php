<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module;

/**
 *列表模板
 */
class Wndt_Admin extends Wnd_Module {

	// 管理面板
	public static function build() {
		$html = '<div id="user-center">';
		$html .= static::build_user_panel_tabs();
		$html .= '<div class="ajax-container">';
		$html .= Wndt_Admin_Setting::build();
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	// 导航Tabs
	protected static function build_user_panel_tabs() {
		$html = '<div id="user-panel-tabs" class="tabs is-fullwidth column is-paddingless">';
		$html .= '<ul>';
		$html .= '<li class="wnd_user_center" class="is-active"><a href="">面板</a></li>';
		$html .= '<li class="wndt_admin_setting"><a href="#wndt_admin_setting">设置</a></li>';
		$html .= '<li class="wnd_account_form"><a href="#wnd_account_form">账户</a></li>';
		$html .= '</ul>';
		$html .= '</div>';
		return $html;
	}
}