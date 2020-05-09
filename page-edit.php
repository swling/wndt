<?php
/**
 * Template Name: 编辑
 **/
get_header();

echo '<main id="content" class="column content">';
echo '<div class="narrow-container box">';

/**
 *发布资源权限判断
 */
$post_id   = $_GET['post_id'] ?? 0;
$edit_post = $post_id ? get_post($post_id) : false;

try {
	$ppc = Wndt\Model\Wndt_PPC::get_instance($edit_post->post_type);
	$ppc->set_post_id($post_id);
	$ppc->check_update();

	// 主题定义的表单
	$class = 'Wndt\Module\\Wndt_' . $edit_post->post_type . '_Form';
	if (class_exists($class)) {
		echo $class::build($post_id);

		// 附件编辑表单
	} elseif ('attachment' == $edit_post->post_type) {
		echo Wnd\Module\Wnd_Attachment_Form::build(['attachment_id' => $post_id]);

		// 文章编辑表单
	} else {
		echo Wnd\Module\Wnd_Default_Post_Form::build(
			[
				'post_id'     => $post_id,
				'post_parent' => $edit_post->post_parent,
				'is_free'     => false,
			]
		);
	}
} catch (Exception $e) {
	echo Wnd\Module\Wnd_Module::build_error_message($e->getMessage());
}

echo '</div>';
echo '</main>';
get_footer();
