<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Filter_User;

/**
 *@since 2020.05.06 封装前端用户列表表格
 *@param $number 每页列表数目
 */
class Wnd_User_List_Table extends Wnd_Module {

	public static function build(int $number = 0) {
		$orderby_args = [
			'label'   => '排序',
			'options' => [
				__('注册时间', 'wnd') => 'registered', //常规排序 date title等
				__('文章数量', 'wnd') => 'post_count', //常规排序 date title等
			],
			'order'   => 'DESC',
		];

		$filter = new Wnd_Filter_User(wnd_doing_ajax());
		$filter->set_number($number ?: 20);
		$filter->add_query(['count_total' => false]);
		$filter->add_search_form();
		$filter->add_orderby_filter($orderby_args);
		$filter->add_status_filter(__('状态', 'wnd'));
		$filter->set_ajax_container('#user-list-table');
		$filter->query();

		return $filter->get_tabs() . '<div id="user-list-table">' . $filter->get_results() . '</div>';
	}
}
