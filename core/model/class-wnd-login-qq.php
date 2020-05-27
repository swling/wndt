<?php
namespace Wnd\Model;

use Exception;

/**
 *@since 2019.09.27
 *QQ登录
 */
class Wnd_Login_QQ extends Wnd_Login_Social {

	/**
	 *创建授权地址
	 */
	public function build_oauth_url() {
		if (!$this->app_id) {
			throw new Exception('未配置APP ID');
		}
		if (!$this->redirect_url) {
			throw new Exception('未配置回调网址：redirect_url');
		}

		$query = http_build_query(
			[
				'client_id'     => $this->app_id,
				'state'         => static::build_state('QQ'),
				'response_type' => 'code',
				'redirect_uri'  => $this->redirect_url,
			]
		);

		return 'https://graph.qq.com/oauth2.0/authorize?' . $query;
	}

	/**
	 *根据授权码请求token
	 */
	protected function get_token() {
		if (!$this->app_key) {
			throw new Exception('未配置APP Key');
		}

		if (!isset($_GET['state'])) {
			throw new Exception('state is empty');
		}

		if (!isset($_GET['code'])) {
			throw new Exception('code is empty');
		}

		// 校验自定义state nonce
		static::check_state_nonce($_GET['state']);

		$query = http_build_query(
			[
				'client_id'     => $this->app_id,
				'client_secret' => $this->app_key,
				'code'          => $_GET['code'],
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $this->redirect_url,
			]
		);

		$token_url = 'https://graph.qq.com/oauth2.0/token?' . $query;

		//获取响应报文
		$response = wp_remote_get($token_url);
		if (is_wp_error($response)) {
			throw new Exception($response->get_error_message());
		}

		//解析报文，获取token
		$response = $response['body'];
		$params   = [];
		parse_str($response, $params);
		$this->token = $params['access_token'] ?? false;
		if (!$this->token) {
			throw new Exception('获取token失败');
		}
	}

	/**
	 *根据token 获取用户QQ open id
	 */
	private function get_open_id() {
		if (!$this->token) {
			throw new Exception('获取token失败');
		}

		$graph_url = 'https://graph.qq.com/oauth2.0/me?access_token=' . $this->token;
		$str       = wp_remote_get($graph_url);
		$str       = $str['body'];
		if (strpos($str, 'callback') !== false) {
			$lpos = strpos($str, '(');
			$rpos = strrpos($str, ')');
			$str  = substr($str, $lpos + 1, $rpos - $lpos - 1);
		}
		$user = json_decode($str, true);
		if (isset($user->error)) {
			echo '<h3>错误代码:</h3>' . $user->error;
			echo '<h3>信息  :</h3>' . $user->error_description;
			exit();
		}
		$this->open_id = $user['openid'];
		if (!$this->open_id) {
			throw new Exception('获取用户open id失败');
		}
	}

	/**
	 *根据token 和 open id获取用户信息
	 */
	protected function get_user_info() {
		$this->get_open_id();

		if (!$this->token or !$this->open_id) {
			throw new Exception('Token 或 open ID为空');
		}

		$query = http_build_query([
			'access_token'       => $this->token,
			'oauth_consumer_key' => $this->app_id,
			'openid'             => $this->open_id,
			'format'             => 'json',
		]);

		$get_info_link = 'https://graph.qq.com/user/get_user_info?' . $query;
		$user_info     = wp_remote_get($get_info_link);
		$user_info     = $user_info['body'];
		$user_info     = json_decode($user_info, true);

		//2.4 组成用户数据
		$this->display_name = $user_info['nickname'];
		$avatar_url         = $user_info['figureurl_qq_2'] ?? $user_info['figureurl_qq_1'];
		$this->avatar_url   = str_replace('http://', 'https://', $avatar_url);
	}
}
