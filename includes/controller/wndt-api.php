<?php
/**
 *@since 2019.06.27 API改造
 */

namespace Wndt\Controller;

class Wndt_API {

	public function __construct() {
		add_action('rest_api_init', [$this, 'register_rest_route']);
	}

	/**
	 *注册路由
	 */
	public function register_rest_route() {
		register_rest_route(
			'wndt',
			'project/(?P<ID>[\d]+)',
			[
				'methods'             => 'GET',
				'callback'            => __CLASS__ . '::handle_project_api',
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 *@since 2019.06.27
	 *@param $request	 	mixed 		请求路由
	 */
	public static function handle_project_api($request): array{
		$post_id = $request['ID'];
		$token   = $_REQUEST['token'] ?? '';

		$post = get_post($post_id);
		if (!$post) {
			return [
				'status' => 0,
				'msg'    => 'ID无效',
			];
		}

		if ('project' != $post->post_type) {
			return [
				'status' => 0,
				'msg'    => '当前ID有效，但并非 Project',
			];
		}

		// 获取文章附件
		$attachment_id = wnd_get_post_meta($post_id, 'file') ?: 0;
		$file_url      = wp_get_attachment_url($attachment_id);

		// 更新包信息
		$data = [
			'new_version' => wnd_get_post_meta($post_id, 'version') ?: '0', //版本号
			'url'         => get_permalink($post), //介绍页面URL
			'package'     => $file_url, //下载地址
		];

		return [
			'status' => 1,
			'data'   => $data,
		];
	}
}
