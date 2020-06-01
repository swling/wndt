<?php
namespace Wndt\Module;

use Wndt\Module\Wndt_User_Overview;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.03.30
 *
 *封装用户中心页面
 *
 */
class Wndt_User_Center extends Wnd_Module {

	public static function build() {
		// 用户属性
		$user    = wp_get_current_user();
		$user_id = $user->ID;
		$html    = '';

		// 未登录用户
		if (!is_user_logged_in()) {
			return Wndt_User_Center_Login::build();
		}

		// 管理员
		if (wnd_is_manager()) {
			return Wndt_User_Center_Manager::build();
		}

		return static::build_user_center();
	}

	// 常规用户面板
	protected static function build_user_center() {
		$html = '<div id="user-center">';
		$html .= static::build_user_panel_tabs();
		$html .= '<div class="ajax-container">';
		$html .= Wndt_User_Overview::build();
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	// 导航Tabs
	protected static function build_user_panel_tabs() {
		$html = '<div id="user-panel-tabs" class="tabs is-fullwidth column is-paddingless">';
		$html .= '<ul>';
		$html .= '<li class="wnd_user_center" class="is-active"><a href="">面板</a></li>';
		$html .= '<li class="wnd_user_posts_panel"><a href="#wnd_user_posts_panel">内容</a></li>';
		$html .= '<li class="wnd_user_finance_panel"><a href="#wnd_user_finance_panel">财务</a></li>';
		$html .= '<li class="wnd_profile_form"><a href="#wnd_profile_form">资料</a></li>';
		$html .= '<li class="wnd_account_form"><a href="#wnd_account_form">账户</a></li>';
		$html .= '<li class="wnd_mail_box"><a href="#wnd_mail_box">';
		$html .= '<span ' . (wnd_get_mail_count() ? 'data-badge="' . wnd_get_mail_count() . '"' : '') . '>消息</span>';
		$html .= '</a></li>';
		$html .= '</ul>';
		$html .= '</div>';
		return $html;
	}
}