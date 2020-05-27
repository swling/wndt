<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Filter;

/**
 *@since 2019.02.19 封装前端当前用户站内信
 *@param $posts_per_page 每页列表数目
 */
class Wnd_Mail_Box extends Wnd_Module {

	public static function build(int $posts_per_page = 0) {
		if (!is_user_logged_in()) {
			return static::build_error_message(__('请登录', 'wnd'));
		}
		$posts_per_page = $posts_per_page ?: get_option('posts_per_page');

		$filter = new Wnd_Filter(wnd_doing_ajax());
		$filter->add_search_form();
		$filter->add_post_type_filter(['mail']);
		$filter->add_post_status_filter([__('全部', 'wnd') => 'any', __('未读', 'wnd') => 'pending', __('已读', 'wnd') => 'private']);
		$filter->add_query(['author' => get_current_user_id()]);
		$filter->set_posts_template('wnd_list_table');
		$filter->set_posts_per_page($posts_per_page);
		$filter->set_ajax_container('#user-mail-panel');
		$filter->query();
		return $filter->get_tabs() . '<div id="user-mail-panel">' . $filter->get_results() . '</div>';

	}
}
