<?php
namespace Wndt\Module;

/**
 *@since 2020.04.27
 *
 *管理员用户中心
 *
 */
class Wndt_User_Center_Manager extends Wndt_User_Center {

	protected static function build($args = []): string {
		return static::build_user_center();
	}

	// 导航Tabs
	protected static function build_user_panel_tabs() {
		$html = '<div id="user-panel-tabs" class="tabs is-fullwidth column is-paddingless">';
		$html .= '<ul>';
		$html .= '<li class="wnd_user_center" class="is-active"><a href="">面板</a></li>';
		$html .= '<li class="wnd_admin_posts_panel"><a href="#wnd_admin_posts_panel">审核</a></li>';
		$html .= '<li class="wnd_admin_finance_panel"><a href="#wnd_admin_finance_panel">统计</a></li>';
		$html .= '<li class="wnd_user_posts_panel"><a href="#wnd_user_posts_panel">内容</a></li>';
		$html .= '<li class="wnd_user_finance_panel"><a href="#wnd_user_finance_panel">财务</a></li>';
		$html .= '<li class="wnd_user_list_table"><a href="#wnd_user_list_table">用户</a></li>';
		$html .= '<li class="wnd_profile_form"><a href="#wnd_profile_form">资料</a></li>';
		$html .= '<li class="wnd_account_form"><a href="#wnd_account_form">账户</a></li>';
		$html .= '</ul>';
		$html .= '</div>';
		return $html;
	}
}
