<?php
namespace Wnd\Template;

use Wnd\View\Wnd_Posts_Table;

/**
 *表格
 *
 *@since 2019.12.18
 */
class Wnd_List_table {

	/**
	 *构建表单
	 */
	public static function build(\WP_Query $query) {
		$method = 'build_' . $query->query_vars['post_type'] . '_table';
		$method = str_replace('-', '_', $method);
		if (method_exists(__CLASS__, $method)) {
			return static::$method($query);
		} else {
			return static::build_post_table($query);
		}
	}

	/**
	 *@since 2019.08.16
	 *常规文章列表
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 **/
	protected static function build_post_table($query) {
		$table = new Wnd_Posts_Table($query, true, true);
		$table->add_column(
			[
				'post_field' => 'post_date',
				'title'      => '日期',
				'class'      => 'is-narrow is-hidden-mobile',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_title_with_link',
				'title'      => '标题',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_status',
				'title'      => '状态',
				'class'      => 'is-narrow',
			]
		);
		$table->build();
		$html = $table->html;
		return $html;
	}

	/**
	 *@since 2019.08.16
	 *用户邮件列表
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 **/
	protected static function build_mail_table($query) {
		$table = new Wnd_Posts_Table($query, true, true);
		$table->add_column(
			[
				'post_field' => 'post_date',
				'title'      => '日期',
				'class'      => 'is-narrow is-hidden-mobile',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_title_with_link',
				'title'      => '标题',
			]
		);
		$table->build();
		$html = $table->html;
		return $html;
	}

	/**
	 *@since 2019.03.14
	 *以表格形式输出用户充值记录
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 */
	protected static function build_recharge_table($query) {
		return static::build_finance_table($query);
	}

	/**
	 *@since 2019.03.14
	 *以表格形式输出用户消费记录
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 */
	protected static function build_order_table($query) {
		return static::build_finance_table($query);
	}

	/**
	 *@since 2019.03.14
	 *以表格形式输出用户消费/充值记录
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 */
	protected static function build_finance_table($query) {
		$table = new Wnd_Posts_Table($query, true, true);
		$table->add_column(
			[
				'post_field' => 'post_date',
				'title'      => '日期',
				'class'      => 'is-narrow is-hidden-mobile',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_author',
				'title'      => '用户',
				'class'      => 'is-narrow',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_content',
				'title'      => '金额',
				'class'      => 'is-narrow',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_parent_with_link',
				'title'      => '详情',
				'class'      => 'is-narrow',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_status',
				'title'      => '状态',
				'class'      => 'is-narrow is-hidden-mobile',
			]
		);
		$table->build();
		return $table->html;
	}

	/**
	 *@since 2019.12.19
	 *
	 *消费统计列表
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 **/
	protected static function build_stats_ex_table($query) {
		return static::build_finance_stats_table($query);
	}

	/**
	 *@since 2019.12.19
	 *
	 *充值统计列表
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 **/
	protected static function build_stats_re_table($query) {
		return static::build_finance_stats_table($query);
	}

	/**
	 *@since 2019.12.19
	 *
	 *财务统计列表
	 *
	 *@param 	object 	$query 	WP_Query 实例化结果
	 *@return 	string 	$html 	输出表单
	 **/
	protected static function build_finance_stats_table($query) {
		$table = new Wnd_Posts_Table($query, true, true);
		$table->add_column(
			[
				'post_field' => 'post_title',
				'title'      => '标题',
				'class'      => 'is-narrow',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_content',
				'title'      => '金额',
			]
		);
		$table->add_column(
			[
				'post_field' => 'post_date',
				'title'      => '创建时间',
				'class'      => 'is-narrow is-hidden-mobile',
			]
		);
		$table->build();
		$html = $table->html;
		return $html;
	}
}
