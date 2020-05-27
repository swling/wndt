<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module;

/**
 *@since 2020.03.29
 *
 *当前账户概览
 */
class Wndt_User_Overview extends Wnd_Module {

	public static function build() {
		$user_id = get_current_user_id();
		$html    = '';

		// 账户概览
		$html .= '<div class="is-divider" data-content="账户概览"></div>';
		$html .= static::build_financial_overview($user_id);

		// 退出按钮
		$html .= '<div class="box has-text-centered is-size-3">';
		$html .= '<a href="' . wp_logout_url(home_url()) . '" title="退出"><i class="fas fa-power-off"></i></a>';
		$html .= '</div>';

		return $html;
	}

	/**
	 *财务概览
	 */
	public static function build_financial_overview($user_id) {
		$user_id = get_current_user_id();

		// 用户余额
		$html = '<div class="level is-mobile has-text-centered">';
		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">余额</p>';
		$html .= '<p>' . wnd_get_user_money($user_id) . '</p>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">消费</p>';
		$html .= '<p>' . wnd_get_user_expense($user_id) . '</p>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">资源</p>';
		$html .= '<p>0篇</p>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level is-mobile">';
		$html .= '<div class="level-item">';
		$html .= '<button class="button is-outlined" onclick="wnd_ajax_modal(\'wnd_user_recharge_form\')">' . __('余额充值', 'wnd') . '</button>';
		$html .= '</div>';

		if (is_super_admin()) {
			$html .= '<div class="level-item">';
			$html .= '<button class="button is-outlined" onclick="wnd_ajax_modal(\'wnd_admin_recharge_form\')">' . __('人工充值', 'wnd') . '</button>';
			$html .= '</div>';
		}
		$html .= '</div>';

		return $html;
	}
}
