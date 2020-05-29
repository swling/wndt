<?php
/**
 *依赖wnd-frontend
 **/
if (!defined('WND_VER')) {
	return;
}

/**
 *@since 2019.07.31
 *自动加载类文件
 *
 */
spl_autoload_register(function ($class) {
	// 常规
	$base_dir              = TEMPLATEPATH . DIRECTORY_SEPARATOR . 'inc';
	$base_prefix           = 'Wndt\\';
	$base_component_prefix = 'Wndt\\Component\\';

	// 拓展插件
	$plugin_dir    = WP_PLUGIN_DIR;
	$plugin_prefix = 'Wndt\Plugin\\';

	/**
	 *插件加载
	 *实例
	 *类名: Wndt\Plugin\Wndt_Demo\Wndt_Demo
	 *路径: /wp-content/plugins/wndt-demo/wndt-demo.php
	 *
	 *component文件夹存储第三方组件，按通用驼峰命名规则
	 * new Wndt\Plugin\Wndt_Demo\Component\AjaxComment;
	 * /wp-content/plugins/wndt-demo/component/AjaxComment.php
	 *
	 * (注意：第三方组件文件及文件目录需要区分大小写)
	 */
	if (0 === stripos($class, $plugin_prefix)) {
		$path = explode('\\Component\\', $class, 2);
		if ($path[1] ?? false) {
			$path = strtolower($path[0]) . DIRECTORY_SEPARATOR . 'component' . DIRECTORY_SEPARATOR . $path[1];
		} else {
			$path = strtolower($path[0]);
		}

		$path = substr($path, strlen($plugin_prefix));
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		$path = str_replace('_', '-', $path);

		$file = $plugin_dir . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($file)) {
			require $file;
		}

		return;
	}

	/**
	 *实例
	 *类名: Wndt\Module\Wndt_Bid_Form
	 *路径: /inc/module/wndt-bid-form.php
	 *
	 *集成的第三方组件，按通用驼峰命名规则
	 *请注意文件及文件夹大小写必须一一对应
	 * new Wndt\Component\AjaxComment;
	 * inc/component/AjaxComment.php
	 *
	 * (注意：第三方组件文件及文件目录需要区分大小写)
	 */
	if (0 === stripos($class, $base_prefix)) {
		$path = explode($base_component_prefix, $class, 2);
		if ($path[1] ?? false) {
			$path = strtolower($base_component_prefix) . $path[1];
		} else {
			$path = strtolower($path[0]);
		}

		$path = substr($path, strlen($base_prefix));
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		$path = str_replace('_', '-', $path);

		$file = $base_dir . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($file)) {
			require $file;
		}

		return;
	}
});

// Init
Wndt\Model\Wndt_Init::instance();
