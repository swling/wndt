<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Filter;

/**
 *@since 2019.02.19 封装前端管理员内容审核平台
 *@param $posts_per_page 每页列表数目
 */
class Wnd_Admin_Posts_Panel extends Wnd_Module {

	public static function build(int $posts_per_page = 0) {
		if (!wnd_is_manager()) {
			return static::build_error_message(__('权限不足', 'wnd'));
		}

		$posts_per_page = $posts_per_page ?: get_option('posts_per_page');
		$filter         = new Wnd_Filter(wnd_doing_ajax());
		$filter->add_search_form();
		$filter->add_post_type_filter(wnd_get_user_panel_post_types(), true);
		$filter->add_post_status_filter([__('待审', 'wnd') => 'pending']);
		$filter->set_posts_template('wnd_list_table');
		$filter->set_posts_per_page($posts_per_page);
		$filter->set_ajax_container('#admin-posts-panel');
		$filter->query();
		return $filter->get_tabs() . '<div id="admin-posts-panel">' . $filter->get_results() . '</div>';
	}
}
