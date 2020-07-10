<?php

namespace Wndt\Utility;

use Wnd\Utility\Wnd_Upgrader_Theme;

/**
 *@since 2020.04.13
 *主题配置
 */
class Wndt_Upgrader extends Wnd_Upgrader_Theme {

	/**
	 *获取更新包详细信息，至少需要完成如下下信息构造：
	 *
	 *	$this->upgrade_info['url'];
	 *	$this->upgrade_info['package'];
	 *	$this->upgrade_info['new_version'];
	 */
	protected function get_remote_info() {
		$url      = 'https://wndwp.com/wp-json/wndt/project/916';
		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			return $response;
		}
		$response = json_decode($response['body'], true);

		if ($response['status'] > 0) {
			$this->upgrade_info = array_merge($this->upgrade_info, $response['data']);
		}
	}
}
