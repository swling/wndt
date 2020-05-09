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
	$base_dir         = TEMPLATEPATH . DIRECTORY_SEPARATOR . 'inc';
	$base_prefix      = 'Wndt\\';
	$component_dir    = $base_dir . DIRECTORY_SEPARATOR . 'component';
	$component_prefix = 'Wndt\Component\\';

	/**
	 *集成的第三方组件，按通用驼峰命名规则
	 *请注意文件及文件夹大小写必须一一对应
	 *
	 * new Wndt\Component\AjaxComment;
	 * inc/component/AjaxComment.php
	 * (注意：第三方组件文件及文件目录需要区分大小写)
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
	 *实例
	 *类名: Wndt\Module\Wndt_Bid_Form
	 *路径: /inc/module/class-wnd-bid-form.php
	 */
	if (0 === stripos($class, $base_prefix)) {
		$class = strtolower($class);
		$path  = substr($class, strlen($base_prefix));
		$path  = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		$path  = str_replace('_', '-', $path);
		$path  = str_replace('wndt-', 'class-wndt-', $path);
		$file  = $base_dir . DIRECTORY_SEPARATOR . $path . '.php';
		if (file_exists($file)) {
			require $file;
		}
	}
});

// Init
Wndt\Model\Wndt_Init::instance();
