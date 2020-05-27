<?php
use Wnd\Model\Wnd_Config;

/**
 *@since 2019.01.21 获取do page地址
 *一个没有空白的WordPress环境，接收或执行一些操作
 *
 *@return string 	url
 */
function wnd_get_do_url() {
	return WND_URL . 'do.php';
}

/**
 *@since 2020.4.13
 *获取配置选项
 */
function wnd_get_config($config_key) {
	return Wnd_Config::get($config_key);
}

/**
 *@since 2019.04.07
 */
function wnd_doing_ajax() {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
		return true;
	} else {
		return false;
	}
}

/**
 *@since 初始化
 *获取用户ip
 *@param 	bool 	$hidden 	是否隐藏IP部分字段
 *@return 	string 	IP address
 */
function wnd_get_user_ip($hidden = false) {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	if ($hidden) {
		return preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/is', "$1.$2.$3.*", $ip);
	} else {
		return $ip;
	}
}

/**
 *@since 初始化
 *搜索引擎判断
 *@return bool 	是否是搜索引擎
 */
function wnd_is_robot() {
	return (
		isset($_SERVER['HTTP_USER_AGENT']) and preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
	);
}

/**
 *@since 2019.01.30
 *获取随机大小写字母和数字组合字符串
 *
 *@param 	int 	$length 	随机字符串长度
 *@return 	string 	随机字符
 */
function wnd_random($length) {
	$chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
	$hash  = '';
	$max   = strlen($chars) - 1;
	for ($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 *@since 初始化
 *生成N位随机数字
 *@param 	int 	$length 	随机字符串长度
 *@return 	string 	随机字符
 */
function wnd_random_code($length = 6) {
	$No = '';
	for ($i = 0; $i < $length; $i++) {
		$No .= mt_rand(0, 9);
	}
	return $No;
}

/**
 *@since 2019.03.04
 *生成包含当前日期信息的高强度的唯一性ID
 *@return 	string 	随机字符
 */
function wnd_generate_order_NO() {
	$today = date('Ymd');
	$rand  = substr(hash('sha256', uniqid(rand(), TRUE)), 0, 10);
	return $today . $rand;
}

/**
 *@since 2019.02.09  验证是否为手机号
 *
 *@param 	string 	$phone 	需要验证的手机号
 *@return 	bool 	是否为合法的手机号码格式
 */
function wnd_is_phone($phone) {
	if ((empty($phone) or !preg_match("/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/", $phone))) {
		return false;
	} else {
		return true;
	}
}

/**
 *复制taxonomy term数据到 另一个 taxonomy下
 *@since 2019.04.30
 *@param 	string 	$old_taxonomy	需要被复制的taxonomy
 *@param 	string 	$new_taxonomy	需要创建的taxonomy
 */
function wnd_copy_taxonomy($old_taxonomy, $new_taxonomy) {
	$terms = get_terms($old_taxonomy, 'hide_empty=0');

	if (!empty($terms) and !is_wp_error($terms)) {
		foreach ($terms as $term) {
			wp_insert_term($term->name, $new_taxonomy);
		}
		unset($term);
	}
}

/**
 *@since 2019.02.19 在当前位置自动生成一个容器，以供ajax嵌入模板
 *@param $template 	string  			被调用函数(必须以 _wnd为前缀)
 *@param $args 		array or string 	传递给被调用模板函数的参数
 */
function wnd_ajax_embed($template, $args = '') {
	$div_id    = 'wnd-embed-' . uniqid();
	$args      = wp_parse_args($args);
	$ajax_args = http_build_query($args);

	$html = '<div id="' . $div_id . '">';
	$html .= '<script>wnd_ajax_embed(\'#' . $div_id . '\',\'' . $template . '\',\'' . $ajax_args . '\')</script>';
	$html .= '</div>';

	return $html;
}

/**
 *@since 2020.01.14
 *
 *获取当前页面URL
 */
function wnd_get_current_url() {
	return ((isset($_SERVER['HTTPS']) and 'on' == $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 *@since 2019.07.17
 *设置默认的异常处理函数
 */
set_exception_handler('wnd_exception_handler');
function wnd_exception_handler($exception) {
	$html = '<article class="column message is-danger">';
	$html .= '<div class="message-header">';
	$html .= '<p>异常</p>';
	$html .= '</div>';
	$html .= '<div class="message-body">' . $exception->getMessage() . '</div>';
	$html .= '</article>';

	echo $html;
}
