<?php
namespace Wndt\Module;

use Exception;
use Wndt\Model\Wndt_PPC;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.05.10
 *
 *发布表单
 */
class Wndt_Post_Submit extends Wnd_Module {

	public static function build($post_type = 'supply') {
		try {
			$ppc = Wndt_PPC::get_instance($post_type);
			$ppc->check_insert();

			// 主题定义的表单
			$class = '\Wndt\Module\\Wndt_' . $post_type . '_Form';
			if (class_exists($class)) {
				return $class::build();
			}

			// 附件编辑表单
			if ('attachment' == $post_type) {
				return \Wnd\Module\Wnd_Attachment_Form::build();
			}

			// 默认Post表单
			return \Wnd\Module\Wnd_Default_Post_Form::build();
		} catch (Exception $e) {
			return static::build_error_message($e->getMessage());
		}
	}
}
