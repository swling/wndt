<?php
namespace Wndt\Module;

use Exception;
use Wndt\Utility\Wndt_PPC;
use Wnd\Module\Wnd_Module;

/**
 *@since 2020.05.10
 *
 *编辑表单
 */
class Wndt_Post_Edit extends Wnd_Module {

	protected static function build($args = []): string{
		$post_id   = $args['post_id'] ?? 0;
		$edit_post = $post_id ? get_post($post_id) : false;
		if (!$edit_post) {
			return static::build_error_message('ID无效');
		}

		try {
			$ppc = Wndt_PPC::get_instance($edit_post->post_type);
			$ppc->set_post_id($post_id);
			$ppc->check_update();

			// 主题定义的表单
			$class = '\Wndt\Module\\Wndt_Post_Form_' . $edit_post->post_type;
			if (class_exists($class)) {
				return $class::render(['post_id' => $post_id]);
			}

			// 附件编辑表单
			if ('attachment' == $edit_post->post_type) {
				return \Wnd\Module\Wnd_Post_Form_Attachment::render(['attachment_id' => $post_id]);
			}

			// 插件默认表单
			$class = '\Wnd\Module\\Wnd_Post_Form_' . $edit_post->post_type;
			if (class_exists($class)) {
				return $class::render(
					[
						'post_id'     => $post_id,
						'post_parent' => $edit_post->post_parent,
						'is_free'     => false,
					]
				);
			}

			// 未找到匹配表单
			return static::build_error_message(__('当前 Post Type 未定义编辑表单', 'wndt'));

		} catch (Exception $e) {
			return static::build_error_message($e->getMessage());
		}
	}
}
