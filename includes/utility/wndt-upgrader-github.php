<?php

namespace Wndt\Utility;

use Wnd\Utility\Wnd_Upgrader_Theme;

/**
 *@since 2020.04.13
 *主题配置
 */
class Wndt_Upgrader_Github extends Wnd_Upgrader_Theme {

	/**
	 *获取更新包详细信息，至少需要完成如下下信息构造：
	 *
	 *	$this->upgrade_info['url'];
	 *	$this->upgrade_info['package'];
	 *	$this->upgrade_info['new_version'];
	 */
	protected function get_remote_info() {
		$url      = 'https://api.github.com/repos/swling/wndt/releases';
		$response = wp_remote_get($url, ['headers' => ['Authorization' => 'token xxx']]);
		if (is_wp_error($response)) {
			return $response;
		}

		$response = json_decode($response['body'], true);
		if (is_array($response)) {
			$response = current($response);
		}

		// 构造安装包信息
		$this->upgrade_info['url']         = $response['html_url'];
		$this->upgrade_info['package']     = $response['zipball_url'];
		$this->upgrade_info['new_version'] = $response['tag_name'];
	}
}
