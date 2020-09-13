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

	protected static function build($post_type = 'supply'): string {
		// 将GET参数自动作为传参
		$args = $_GET;

		try {
			$ppc = Wndt_PPC::get_instance($post_type);
			$ppc->check_insert();

			// 主题定义的表单
			$class = '\Wndt\Module\\Wndt_Post_Form_' . $post_type;
			if (class_exists($class)) {
				return $class::render($args);
			}

			// 附件编辑表单
			if ('attachment' == $post_type) {
				return \Wnd\Module\Wnd_Attachment_Form::render();
			}

			// 默认Post表单
			return \Wndt\Module\Wndt_Post_Form::render($args);
		} catch (Exception $e) {
			return static::build_error_message($e->getMessage());
		}
	}
}
