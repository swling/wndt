<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Filter;

/**
 *@since 2019.02.18 封装用户财务中心
 *@param $posts_per_page 每页列表数目
 */
class Wnd_User_Finance_Panel extends Wnd_Module {

	public static function build(int $posts_per_page = 0) {
		$user_id        = get_current_user_id();
		$posts_per_page = $posts_per_page ?: get_option('posts_per_page');
		if (!$user_id) {
			return static::build_error_message(__('请登录', 'wnd'));
		}

		$html = '<div id="user-finance-panel">';
		$html .= '<nav class="level is-mobile">';
		$html .= '
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">' . __('余额', 'wnd') . '</p>
				<p class="title">' . wnd_get_user_money($user_id) . '</p>
			</div>
		</div>';

		$html .= '
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">' . __('消费', 'wnd') . '</p>
				<p class="title">' . wnd_get_user_expense($user_id) . '</p>
			</div>
		</div>';

		if (wnd_get_config('commission_rate')) {
			$html .= '
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">' . __('佣金', 'wnd') . '</p>
				<p class="title">' . wnd_get_user_commission($user_id) . '</p>
			</div>
		</div>';
		}
		$html .= '</nav>';

		$html .= '<div class="level is-mobile">';
		$html .= '<div class="level-item">' . wnd_modal_button(__('余额充值', 'wnd'), 'wnd_user_recharge_form') . '</div>';

		if (is_super_admin()) {
			$html .= '<div class="level-item">' . wnd_modal_button(__('人工充值', 'wnd'), 'wnd_admin_recharge_form') . '</div>';
		}
		$html .= '</div>';

		$filter = new Wnd_Filter(wnd_doing_ajax());
		$filter->add_search_form();
		$filter->add_post_type_filter(['order', 'recharge']);
		$filter->add_post_status_filter(['any']);
		$filter->add_query(['author' => get_current_user_id()]);
		$filter->set_posts_template('wnd_list_table');
		$filter->set_posts_per_page($posts_per_page);
		$filter->set_ajax_container('#admin-fin-panel');
		$filter->query();
		$filter_html = $filter->get_tabs() . '<div id="admin-fin-panel">' . $filter->get_results() . '</div>';

		return $html . $filter_html;
	}
}
