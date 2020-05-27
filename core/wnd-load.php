<?php
/**
 *@since 2019.07.31
 *自动加载类文件
 *
 * 本插件：
 * new Wnd\Model\Wnd_Auth;
 * 对应文件路径
 * includes/model/class-wnd-auth.php
 *
 * 第三方组件：
 * new Wnd\Component\Aliyun\Sms\SignatureHelper;
 * includes/component/Aliyun/Sms/SignatureHelper.php
 * (注意：第三方组件文件及文件目录需要区分大小写)
 */
spl_autoload_register(function ($class) {
	// 命名空间前缀及对应目录
	$base_prefix      = 'Wnd\\';
	$component_prefix = 'Wnd\Component\\';
	$base_dir         = WND_PATH . DIRECTORY_SEPARATOR;
	$component_dir    = WND_PATH . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'component';

	/**
	 *本插件集成的第三方组件，按通用驼峰命名规则
	 *请注意文件及文件夹大小写必须一一对应
	 */
	if (0 === stripos($class, $component_prefix)) {
		$path = substr($class, strlen($component_prefix));
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

		$file = $component_dir . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($file)) {
			require $file;
		}

		return;
	}

	/**
	 *本插件类规则
	 */
	if (0 === stripos($class, $base_prefix)) {
		$class = strtolower($class);
		$path  = substr($class, strlen($base_prefix));
		$path  = str_replace('_', '-', $path);
		$path  = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		$path  = str_replace('wnd-', 'class-wnd-', $path);

		$file = $base_dir . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($file)) {
			require $file;
		}
	}
});

// 初始化
Wnd\Model\Wnd_Init::instance();
