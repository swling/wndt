<?php
namespace Wnd\Module;

/**
 *@since 2019.02.16 封装：用户中心
 *@param string or array ：
 *do => register / login / reset_password, tab => string :profile / account, type => email / phone
 *@return $html .= el
 */
class Wnd_User_Center extends Wnd_Module {

	public static function build($args = []) {
		$ajax_type         = $_GET['ajax_type'] ?? 'modal';
		$enable_sms        = (1 == wnd_get_config('enable_sms')) ? true : false;
		$disable_email_reg = (1 == wnd_get_config('disable_email_reg')) ? true : false;
		$is_user_logged_in = is_user_logged_in();

		// 默认参数
		$defaults = [
			'do'   => 'register',
			'tab'  => 'profile',
			'type' => $enable_sms ? 'phone' : 'email',
			'wrap' => true,
		];

		/**
		 *@see 2019.08.17
		 *在非ajax环境中，约定了GET参数，实现切换模块切换，故此，需要确保GET参数优先级
		 **/
		$args = wp_parse_args($args, $defaults, $_GET);
		extract($args);

		/**
		 *重置密码面板：同时允许已登录及未登录用户
		 */
		if ('reset_password' == $do) {
			$html = $wrap ? '<div id="user-center">' : '';
			$html .= Wnd_Reset_Password_Form::build($type);
			$html .= '<div class="has-text-centered">';

			if (wnd_doing_ajax()) {
				if ('email' == $type and $enable_sms) {
					$html .= static::build_module_link('do=reset_password&type=phone', __('手机验证找回', 'wnd'), $ajax_type);
				} elseif ($type == 'phone') {
					$html .= static::build_module_link('do=reset_password&type=email', __('邮箱验证找回', 'wnd'), $ajax_type);
				}

				if (!$is_user_logged_in) {
					$html .= $enable_sms ? ' | ' : '';
					$html .= static::build_module_link('do=login', __('登录', 'wnd'), $ajax_type);
				}

			} else {
				if ('email' == $type and $enable_sms) {
					$html .= '<a href="' . add_query_arg('type', 'phone') . '">' . __('手机验证找回', 'wnd') . '</a>';
				} elseif ($type == 'phone') {
					$html .= '<a href="' . add_query_arg('type', 'email') . '">' . __('邮箱验证找回', 'wnd') . '</a>';
				}

				if (!$is_user_logged_in) {
					$html .= $enable_sms ? ' | ' : '';
					$html .= '<a href="' . add_query_arg('do', 'login') . '">' . __('登录', 'wnd') . '</a>';
				}
			}

			$html .= '</div>';
			$html .= $wrap ? '</div>' : '';
			return $html;
		}

		/**
		 *其他面板
		 */
		$html = $wrap ? '<div id="user-center">' : '';
		if (!$is_user_logged_in) {
			switch ($do) {
			case 'register':
				$html .= Wnd_Reg_Form::build($type);
				$html .= '<div class="has-text-centered">';
				if (wnd_doing_ajax()) {
					if ('email' == $type and $enable_sms) {
						$html .= static::build_module_link('do=register&type=phone', __('手机注册', 'wnd'), $ajax_type) . ' | ';
					} elseif ($type == 'phone' and !$disable_email_reg) {
						$html .= static::build_module_link('do=register&type=email', __('邮箱注册', 'wnd'), $ajax_type) . '|';
					}

					$html .= static::build_module_link('do=login', __('登录', 'wnd'), $ajax_type);

				} else {
					if ('email' == $type and $enable_sms) {
						$html .= '<a href="' . add_query_arg('type', 'phone') . '">' . __('手机注册', 'wnd') . '</a> | ';
					} elseif ($type == 'phone' and !$disable_email_reg) {
						$html .= '<a href="' . add_query_arg('type', 'email') . '">' . __('邮箱注册', 'wnd') . '</a> | ';
					}
					$html .= '<a href="' . add_query_arg('do', 'login') . '">' . __('登录', 'wnd') . '</a>';

				}
				$html .= '</div>';
				break;

			default:
			case 'login':
				$html .= Wnd_Login_Form::build();
				$html .= '<div class="has-text-centered">';
				if (wnd_doing_ajax()) {
					$html .= static::build_module_link('do=register', __('立即注册', 'wnd'), $ajax_type) . ' | ';
					$html .= static::build_module_link('do=reset_password', __('忘记密码', 'wnd'), $ajax_type);
				} else {
					$html .= '<a href="' . add_query_arg('do', 'register') . '">' . __('立即注册', 'wnd') . '</a> | ';
					$html .= '<a href="' . add_query_arg('do', 'reset_password') . '">' . __('忘记密码', 'wnd') . '</a>';
				}
				$html .= '</div>';
				break;
			}

		} else {
			switch ($tab) {
			default:
			case 'profile':
				$html .= '<div class="tabs is-boxed is-centered"><ul class="tab">';
				if (wnd_doing_ajax()) {
					$html .= '<li class="is-active">' . static::build_module_link('tab=profile', __('资料', 'wnd'), $ajax_type) . '</li>';
					$html .= '<li>' . static::build_module_link('tab=account', __('账户', 'wnd'), $ajax_type) . '</li>';
				} else {
					$html .= '<li class="is-active"><a href="' . add_query_arg('tab', 'profile') . '">' . __('资料', 'wnd') . '</a></li>';
					$html .= '<li><a href="' . add_query_arg('tab', 'account') . '">' . __('账户', 'wnd') . '</a></li>';
				}
				$html .= '</ul></div>';
				$html .= Wnd_Profile_Form::build();
				break;

			case 'account':
				$html .= '<div class="tabs is-boxed is-centered"><ul class="tab">';
				if (wnd_doing_ajax()) {
					$html .= '<li>' . static::build_module_link('tab=profile', __('资料', 'wnd'), $ajax_type) . '</li>';
					$html .= '<li class="is-active">' . static::build_module_link('tab=account', __('账户', 'wnd'), $ajax_type) . '</li>';
				} else {
					$html .= '<li><a href="' . add_query_arg('tab', 'profile') . '">' . __('资料', 'wnd') . '</a></li>';
					$html .= '<li class="is-active"><a href="' . add_query_arg('tab', 'account') . '">' . __('账户', 'wnd') . '</a></li>';
				}
				$html .= '</ul></div>';
				$html .= Wnd_Account_Form::build();
				break;
			}
		}

		$html .= $wrap ? '</div>' : '';
		return $html;
	}

	/**
	 *构建用户中心模块切换链接
	 *@since 2020.04.23
	 */
	public static function build_module_link($args, $text, $ajax_type) {
		$defaults['wrap'] = '0';
		$args             = http_build_query(wp_parse_args($args, $defaults));

		if ('embed' == $ajax_type) {
			return '<a onclick="wnd_ajax_embed(\'#user-center\',\'wnd_user_center\',\'' . $args . '\');">' . $text . '</a>';
		} elseif ('modal' == $ajax_type) {
			return '<a onclick="wnd_ajax_modal(\'wnd_user_center\',\'' . $args . '\');">' . $text . '</a>';
		}
	}
}
