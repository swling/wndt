<?php
namespace Wndt\Module;

use Exception;
use Wndt\Utility\Wndt_PPC;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.05.10
 *
 *发布表单
 */
class Wndt_Post_Submit extends Wnd_Module {

	protected static function build($args = []): string{
		$post_type = $args['type'] ?? 'post';

		try {
			$ppc = Wndt_PPC::get_instance($post_type);
			$ppc->check_insert();

			// 主题定义的表单
			$class = '\Wndt\Module\\Wndt_Post_Form_' . $post_type;
			if (class_exists($class)) {
				return $class::render();
			}

			// 插件默认表单
			$class = '\Wnd\Module\\Wnd_Post_Form_' . $post_type;
			if (class_exists($class)) {
				return $class::render();
			}

			// 未找到匹配表单
			return static::build_error_message(__('当前 Post Type 未定义编辑表单', 'wndt'));

		} catch (Exception $e) {
			return static::build_error_message($e->getMessage());
		}
	}
}
