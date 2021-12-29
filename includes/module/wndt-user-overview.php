<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module_Html;

/**
 * 当前账户概览
 * @since 2020.03.29
 */
class Wndt_User_Overview extends Wnd_Module_Html {

	protected static function build($args = []): string{
		$user_id       = get_current_user_id();
		$vip_timestamp = wndt_get_vip_timestamp($user_id);
		$html          = '';

		// 用户余额
		$html .= '<div class="level is-mobile has-text-centered mt-5">';
		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">余额</p>';
		$html .= '<p>' . wnd_get_user_balance($user_id, true) . '</p>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">消费</p>';
		$html .= '<p>' . wnd_get_user_expense($user_id, true) . '</p>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level-item">';
		$html .= '<div>';
		$html .= '<p class="heading">VIP会员</p>';
		$html .= '<p>' . $vip_timestamp ? date('Y-m-d', $vip_timestamp) : '非VIP' . '</p>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="level is-mobile">';
		$html .= '<div class="level-item">';
		$html .= wnd_modal_button(__('余额充值', 'wndt'), 'wnd_user_recharge_form');
		$html .= '</div>';

		if (is_super_admin()) {
			$html .= '<div class="level-item">';
			$html .= wnd_modal_button(__('人工充值', 'wndt'), 'wnd_admin_recharge_form');
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= '<div class="level is-mobile">';
		$html .= '<div class="level-item">';
		$html .= wnd_get_user_locale($user_id);
		$html .= '</div>';
		$html .= '</div>';

		// 退出按钮
		$html .= '<div class="has-text-centered is-size-3">';
		$html .= '<a href="' . wp_logout_url(home_url()) . '" title="退出"><span class="icon"><i class="fas fa-power-off"></i></span></a>';
		$html .= '</div>';

		return $html;
	}
}
