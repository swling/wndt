<?php
use Wndt\Permission\Wndt_FSC;
use Wndt\Permission\Wndt_PPC;

/**
 *@see wndwp/READEME.md
 * ############################################################################ 以下为WndWP插件过滤钩子
 */

/**
 *插入文章后，返回值过滤
 *@since 2019.01.18 匿名发布
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
 *@since 初始化
 * 写入文章权限检测
 */
add_filter('wnd_can_insert_post', 'wndt_filter_can_insert_post', 11, 3);
function wndt_filter_can_insert_post($can_array, $post_type, $update_id) {
	try {
		$ppc = Wndt_PPC::get_instance($post_type);

		// 可能会校验标题是否重复
		$ppc->set_post_title($_POST['_post_post_title'] ?? '');

		// 定义更新：已指定post id，且排除自动草稿
		if ($update_id and get_post_status($update_id) != 'auto-draft') {
			$ppc->set_post_id($update_id);
			$ppc->check_update();
		} else {
			$ppc->check_insert();
		}
	} catch (Exception $e) {
		return ['status' => 0, 'msg' => $e->getMessage()];
	}

	return $can_array;
}

/**
 *@since 12.22
 *表单提交控制
 */
add_filter('wnd_request_controller', 'wndt_filter_can_submit_form', 11, 2);
function wndt_filter_can_submit_form($can_array, $request) {
	try {
		Wndt_FSC::check($request);
	} catch (Exception $e) {
		return ['status' => 0, 'msg' => $e->getMessage()];
	}

	return $can_array;
}

/**
 *@since 初始化 是否可以更新状态
 */
add_filter('wnd_can_update_post_status', 'wndt_filter_can_update_post_status', 11, 3);
function wndt_filter_can_update_post_status($can_array, $before_post, $after_status) {
	try {
		$ppc = Wndt_PPC::get_instance($before_post->post_type);
		$ppc->set_post_id($before_post->ID);
		$ppc->set_post_status($after_status);
		$ppc->check_status_update();
	} catch (Exception $e) {
		return ['status' => 0, 'msg' => $e->getMessage()];
	}

	// 返回默认值
	return $can_array;
}

/**
 *@since 2019.02.13
 */
// return apply_filters('wnd_post_price', $price, $post_id);
add_filter('wnd_get_post_price', 'wndt_filter_get_post_price', 11, 2);
function wndt_filter_get_post_price($price, $post_id) {
	return $price;
}

/**
 *@since 2019.02.19 过滤用户面板 post types
 */
add_filter('wnd_user_panel_post_types', 'wndt_filter_user_panel_post_types', 11, 1);
function wndt_filter_user_panel_post_types($post_types) {
	return $post_types;
}

/**
 *@since 2019.03.19
 *
 *过滤表单提交数据
 *
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
 *@since 2019.04.16 文件上传权限
 */
add_filter('wnd_can_upload_file', 'wndt_filter_upload_file', 11, 3);
function wndt_filter_upload_file($can_array, $post_parent, $meta_key) {
	try {
		$upc = new Wndt_PPC();
		$upc->check_file_upload($post_parent, $meta_key);
	} catch (Exception $e) {
		return ['status' => 0, 'msg' => $e->getMessage()];
	}
	// 返回未经修改的默认值
	return $can_array;
}

/**
 *@since 2019.07.11 新增社交登录
 **/
add_filter('Wnd\Module\Wnd_Login_Form', 'wndt_filter_login_form', 12, 1);
function wndt_filter_login_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Login_Social::get_instance('QQ')->build_oauth_url();
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
 *@since 2019.07.11 新增社交注册
 **/
add_filter('Wnd\Module\Wnd_Reg_Form', 'wndt_filter_reg_form', 12, 1);
function wndt_filter_reg_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Login_Social::get_instance('QQ')->build_oauth_url();
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
 *@since 2019.07.11 绑定QQ
 **/
add_filter('Wnd\Module\Wnd_Account_Form', 'wndt_filter_account_form', 12, 1);
function wndt_filter_account_form($input_fiels) {
	try {
		$oauth_url = Wnd\Getway\Wnd_Login_Social::get_instance('QQ')->build_oauth_url();
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
 *自定义用户菜单
 *
 */
add_filter('wnd_menus', function ($menus, $args) {
	return \Wndt\Jsonget\Wndt_User_Menus::get($args);
}, 12, 2);

/**
 *主题新增的赞赏交易类型
 *@since 2021.04.27
 */
// $instance = apply_filters('wnd_transaction_instance', $instance, $type, $this->object_id);
add_filter('wnd_transaction_instance', function ($instance, $type) {
	if ('reward' == $type) {
		return new Wndt\Model\Wndt_Reward();
	}

	return $instance;

}, 12, 2);
