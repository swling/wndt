<?php
namespace Wndt\Module;

use Exception;
use Wndt\Utility\Wndt_PPC;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.10.14
 *
 *内容编辑表抽象类：权限检测
 */
abstract class Wndt_Post_Form extends Wnd_Module {

	/**
	 *权限核查
	 */
	protected static function check($args) {
		$post_type = $args['type'] ?? 'post';
		$post_id   = $args['post_id'] ?? 0;

		// 更新权限检测
		if ($post_id) {
			$edit_post = $post_id ? get_post($post_id) : false;
			if (!$edit_post) {
				throw new Exception(static::build_error_notification(__('ID 无效', 'wndt'), true));
			}

			$ppc = Wndt_PPC::get_instance($edit_post->post_type);
			$ppc->set_post_id($post_id);
			$ppc->check_update();

			// 发布权限检测
		} else {
			$ppc = Wndt_PPC::get_instance($post_type);
			$ppc->check_insert();
		}
	}
}
