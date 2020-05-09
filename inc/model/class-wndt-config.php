<?php
namespace Wndt\Model;

use Wnd\Model\Wnd_Config;

/**
 *@since 2020.04.13
 *主题配置
 */
class Wndt_Config extends Wnd_Config {

	/**
	 *WP option name
	 */
	protected static $wp_option_name = 'wndt';

	/**
	 *option数组键名统一前缀
	 */
	protected static $config_key_prefix = 'wndt_';
}
