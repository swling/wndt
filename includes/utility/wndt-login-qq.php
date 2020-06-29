<?php
namespace Wndt\Utility;

use Exception;
use Wnd\Utility\Wnd_Login_Social;

/**
 *@since 2020.03.24
 *
 *封装QQ登录
 */
class Wndt_Login_QQ extends Wndt_Login_Social {

	/**
	 *回调地址，需要与QQ互联的设置保持一致
	 */
	public static function build_redirect_url() {
		return add_query_arg(
			[
				'type' => 'qq',
			],
			wndt_get_config('social_redirect_url')
		);
	}

	/**
	 *构建QQ登录链接
	 *
	 *@since 2020.03.24
	 */
	public static function build_oauth_link($text = 'QQ登录') {
		try {
			$qq_login = Wnd_Login_Social::get_instance('QQ');
			$qq_login->set_app_id(wndt_get_config('qq_appid'));
			$qq_login->set_redirect_url(static::build_redirect_url());

			$html = '<div class="has-text-centered field is-size-5">';
			$html .= '<a class="qq" href="' . $qq_login->build_oauth_url() . '"><i class="fab fa-qq"></i>&nbsp;' . $text . '</a>';
			$html .= '</div>';
			return $html;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 *监听QQ登录回调，并注册或登录账户
	 *
	 *@since 2020.03.24
	 */
	public static function login() {
		try {
			$qq_login = Wnd_Login_Social::get_instance('QQ');
			$qq_login->set_app_id(wndt_get_config('qq_appid'));
			$qq_login->set_app_key(wndt_get_config('qq_appkey'));
			$qq_login->set_redirect_url(static::build_redirect_url());
			$qq_login->login();
		} catch (Exception $e) {
			wp_die($e->getMessage(), bloginfo('name'));
		}
	}
}
