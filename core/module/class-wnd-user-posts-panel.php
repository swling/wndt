<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Filter;

/**
 *@since 2019.02.19 封装前端当前用户内容管理面板
 *@param $posts_per_page 每页列表数目
 */
class Wnd_User_Posts_Panel extends Wnd_Module {

	public static function build(int $posts_per_page = 0) {
		if (!is_user_logged_in()) {
			return static::build_error_message(__('请登录', 'wnd'));
		}

		$posts_per_page = $posts_per_page ?: get_option('posts_per_page');
		$filter         = new Wnd_Filter(wnd_doing_ajax());
		$filter->add_search_form();
		$filter->add_post_type_filter(wnd_get_user_panel_post_types());
		$filter->add_post_status_filter([__('全部', 'wnd') => 'any', __('发布', 'wnd') => 'publish', __('待审', 'wnd') => 'pending', __('关闭', 'wnd') => 'close', __('草稿', 'wnd') => 'draft']);
		$filter->add_taxonomy_filter(['taxonomy' => $filter->category_taxonomy]);
		$filter->add_query(['author' => get_current_user_id()]);
		$filter->set_posts_template('wnd_list_table');
		$filter->set_posts_per_page($posts_per_page);
		$filter->set_ajax_container('#user-posts-panel');
		$filter->query();
		return $filter->get_tabs() . '<div id="user-posts-panel">' . $filter->get_results() . '</div>';
	}
}
