<?php
namespace Wndt\Utility;

use Wnd\Utility\Wnd_Config;

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
	 *filter_prefix
	 */
	protected static $filter_prefix = 'wndt_option_';
}
