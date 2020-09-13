<?php
namespace Wndt\Module;

use Wndt\Module\Wndt_User_Overview;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.03.23
 *
 *侧边栏菜单
 */
class Wndt_Sidebar_Menu extends Wnd_Module {

	protected static function build() {
		//未登录用户
		if (!is_user_logged_in()) {
			$html = '<aside id="sidebar-menu">';
			$html .= '<div class="navbar-burger navbar-brand is-pulled-right is-active">';
			$html .= '<span></span><span></span><span></span>';
			$html .= '</div>';

			$html .= '<div class="has-text-centered">';
			$html .= '<a class="button is-black" onclick="wnd_ajax_modal(\'wnd_user_center\')">免费注册</a>';
			$html .= '&nbsp;<a class="button is-danger is-outlined" onclick="wnd_ajax_modal(\'wnd_login_form\')">立即登录</a>';
			$html .= '</div>';
			$html .= '</aside>';
			return $html;
		}

		$html = '<aside id="sidebar-menu">';
		$html .= '<div class="navbar-burger navbar-brand is-pulled-right is-active">';
		$html .= '<span></span><span></span><span></span>';
		$html .= '</div>';

		$html .= '<div class="menu">';
		$html .= '<ul class="menu-list">';
		$html .= '<li>管理</li>';
		$html .= '<li>';
		$html .= '<ul>';
		$html .= '<li><a href="' . home_url('ucenter') . '">面板</a></li>';
		if (wnd_is_manager()) {
			$html .= '<li><a href="' . home_url('ucenter') . '#wnd_admin_posts_panel">审核</a></li>';
			$html .= '<li><a href="' . home_url('ucenter') . '#wnd_admin_finance_panel">统计</a></li>';
		}
		$html .= '<li><a href="' . home_url('ucenter') . '#wnd_mail_box">';
		$html .= '<span ' . (wnd_get_mail_count() ? 'data-badge="' . wnd_get_mail_count() . '"' : '') . '>消息</span>';
		$html .= '</a></li>';
		$html .= '<li><a href="' . home_url('ucenter') . '#wnd_user_posts_panel">内容</a></li>';
		$html .= '<li><a href="' . home_url('ucenter') . '#wnd_user_finance_panel">财务</a></li>';
		$html .= '</ul>';
		$html .= '</li>';

		$html .= '<li>账户</li>';
		$html .= '<li>';
		$html .= '<ul>';
		$html .= '<li><a href="' . home_url('ucenter') . '#wnd_user_center">账户</a></li>';
		$html .= '<li><a href="' . home_url('ucenter') . '#wndt_user_license_form">资质</a></li>';
		$html .= '</ul>';
		$html .= '</li>';

		$html .= '</ul>';
		$html .= '</div>';
		$html .= Wndt_User_Overview::render();
		$html .= '</aside>';

		return $html;
	}
}
