<?php
use Wndt\Permission\Wndt_FSC;

/**
 * 内容权限控制
 */
add_filter('wnd_ppc_instance', function ($instance, $post_type) {
	$ppc_class_name = 'Wndt\\Permission\\Wndt_PPC_' . $post_type;
	if (class_exists($ppc_class_name)) {
		return new $ppc_class_name($post_type);
	}

	return $instance;

}, 11, 2);

/**
 * 插入文章后，返回值过滤
 * @since 2019.01.18 匿名发布
 */
add_filter('wnd_insert_post_return', 'wndt_filter_insert_post_return', 11, 3);
function wndt_filter_insert_post_return($can_array, $post_type, $post_id) {
	// 未登录用户发布需求成功
	if ($can_array['status'] > 0 and in_array($post_type, ['demand', 'transaction'])) {
		$can_array['status'] = 1;
		$can_array['msg']    = '提交成功，请等待审核！<a href="' . get_permalink($post_id) . '">查看详情</a>';
	}

	// 返回默认值
	return $can_array;
}

/**
 * 表单提交控制
 * @since 12.22
 */
add_filter('wnd_request_controller', 'wndt_filter_can_submit_form', 11, 3);
function wndt_filter_can_submit_form($can_array, $request, $route) {
	try {
		Wndt_FSC::check($request, $route);
	} catch (Exception $e) {
		return ['status' => 0, 'msg' => $e->getMessage()];
	}

	return $can_array;
}

/**
 * @since 2019.02.13
 */
// return apply_filters('wnd_post_price', $price, $post_id);
add_filter('wnd_get_post_price', 'wndt_filter_get_post_price', 11, 2);
function wndt_filter_get_post_price($price, $post_id) {
	return $price;
}

/**
 * @since 2019.02.19 过滤用户面板 post types
 */
add_filter('wnd_user_panel_post_types', 'wndt_filter_user_panel_post_types', 11, 1);
function wndt_filter_user_panel_post_types($post_types) {
	return $post_types;
}

/**
 * 过滤表单提交数据
 * @since 2019.03.19
 */
add_filter('wnd_request', 'wndt_filter_request', 11, 1);
function wndt_filter_request($request) {
	// 需求自动设置title
	if (isset($request['_post_post_type']) and $request['_post_post_type'] == 'demand') {
		$cat_name                    = get_term($request['_term_demand_cat'])->name;
		$request['_post_post_title'] = '需求：' . $cat_name . ' [' . ($request['_post_ID'] ?: '匿名用户') . ']';
	}

	// 替换标签设置中的中文逗号
	if (isset($request['_term_company_tag'])) {
		$request['_term_company_tag'] = str_replace('，', ',', $request['_term_company_tag']);
	} elseif (isset($request['_term_people_tag'])) {
		$request['_term_people_tag'] = str_replace('，', ',', $request['_term_people_tag']);
	}

	return $request;
}

/**
 * @since 2019.07.11 新增社交登录
 */
add_filter('Wnd\Module\User\Wnd_Login_Form', 'wndt_filter_login_form', 12, 1);
function wndt_filter_login_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Social_Login_Builder::get_instance('QQ')->build_oauth_url();
		$html      = '<div class="has-text-centered field is-size-5">';
		$html .= '<a class="qq" href="' . $oauth_url . '"><i class="fab fa-qq"></i>&nbsp;QQ 登录</a>';
		$html .= '</div>';

		$form = new Wnd\View\Wnd_Form_WP();
		$form->add_html($html);
		return array_merge($input_fiels, $form->get_input_values());
	} catch (Exception $e) {
		return $input_fiels;
	}
}

/**
 * @since 2019.07.11 新增社交注册
 */
add_filter('Wnd\Module\User\Wnd_Reg_Form', 'wndt_filter_reg_form', 12, 1);
function wndt_filter_reg_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Social_Login_Builder::get_instance('QQ')->build_oauth_url();
		$html      = '<div class="has-text-centered field is-size-5">';
		$html .= '<a class="qq" href="' . $oauth_url . '"><i class="fab fa-qq"></i>&nbsp;QQ 注册</a>';
		$html .= '</div>';

		$form = new Wnd\View\Wnd_Form_WP();
		$form->add_html($html);
		return array_merge($input_fiels, $form->get_input_values());
	} catch (Exception $e) {
		return $input_fiels;
	}
}

/**
 * @since 2019.07.11 绑定QQ
 */
add_filter('Wnd\Module\User\Wnd_Account_Form', 'wndt_filter_account_form', 12, 1);
function wndt_filter_account_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Social_Login_Builder::get_instance('QQ')->build_oauth_url();
		$html      = '<div class="has-text-centered field is-size-5">';
		$html .= '<a class="qq" href="' . $oauth_url . '"><i class="fab fa-qq"></i>&nbsp;绑定 QQ</a>';
		$html .= '</div>';

		$form = new Wnd\View\Wnd_Form_WP();
		$form->add_html($html);
		return array_merge($input_fiels, $form->get_input_values());
	} catch (Exception $e) {
		return $input_fiels;
	}
}

/**
 * 自定义用户菜单
 *
 */
// add_filter('wnd_menus', function ($menus, $args) {
// 	if ($args['in_side'] or !wnd_is_manager()) {
// 		return $menus;
// 	}

// 	$menus[] = [
// 		'name' => '赞赏',
// 		'hash' => 'wndt_reward_list',
// 		'icon' => '<i class="fas fa-paperclip"></i>', // 附件
// 	];
// 	return $menus;
// }, 11, 2);

/**
 * 侧边栏菜单后续
 */
add_filter('wnd_menus_side_after', function ($menus_after) {
	if (!is_user_logged_in()) {
		return $menus_after;
	}

	try {
		return $menus_after . Wndt\Module\Wndt_User_Overview::render();
	} catch (\Exception $e) {
		return $menus_after;
	}

}, 11, 1);

/**
 * 主题新增的赞赏交易类型
 * @since 2021.04.27
 */
// $instance = apply_filters('wnd_transaction_instance', $instance, $type);
add_filter('wnd_transaction_instance', function ($instance, $type) {
	if ('reward' == $type) {
		return new Wndt\Model\Wndt_Reward();
	}

	return $instance;

}, 11, 2);
