<?php
if (!defined('WND_PATH')) {
	require 'wnd-frontend.php';
}

/**
 *@since 2019.07.31
 *自动加载类文件
 *
 * 本插件：
 * new Wnd\Model\Wnd_Auth;
 * 对应文件路径
 * includes/model/wnd-auth.php
 *
 * 第三方组件：
 * new Wnd\Component\Aliyun\Sms\SignatureHelper;
 * includes/component/Aliyun/Sms/SignatureHelper.php
 * (注意：第三方组件文件及文件目录需要区分大小写)
 */
spl_autoload_register(function ($class) {
	$base_prefix      = 'Wnd\\';
	$component_prefix = 'Wnd\\Component\\';
	$base_dir         = WND_PATH;

	if (0 !== stripos($class, $base_prefix)) {
		return;
	}

	// component文件夹中加载第三方组件
	$path = explode($component_prefix, $class, 2);
	if ($path[1] ?? false) {
		$path = strtolower($component_prefix) . $path[1];
	} else {
		$path = strtolower($path[0]);
	}

	$path = substr($path, strlen($base_prefix));
	$path = str_replace('_', '-', $path);
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

	$file = $base_dir . DIRECTORY_SEPARATOR . $path . '.php';
	if (file_exists($file)) {
		require $file;
	}
});

// 初始化
Wnd\Model\Wnd_Init::instance();
