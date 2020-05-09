<?php
namespace Wndt\Model;

use Exception;
use Wnd\Model\Wnd_Login_Social;

/**
 *@since 2020.03.24
 *
 *封装社交登录
 */
abstract class Wndt_Login_Social {

	/**
	 *根据state 校验nonce并自动选择子类
	 */
	public static function get_instance($state) {
		$domain     = Wnd_Login_Social::parse_state($state)['domain'];
		$class_name = __NAMESPACE__ . '\\' . 'Wndt_Login_' . $domain;
		if (class_exists($class_name)) {
			return new $class_name();
		} else {
			throw new Exception(__('指定社交登录类未定义：Wndt', 'wndt'));
		}
	}

	abstract public static function build_oauth_link($text);

	abstract public static function login();
}
