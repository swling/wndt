<?php
/**
 * Template Name: 前端提交
 **/
get_header();

echo '<main id="content" class="column"><div class="narrow-container box">';

/**
 *发布资源权限判断
 */
$post_type = $_GET['type'] ?? 'post';

try {
	$ppc = Wndt\Model\Wndt_PPC::get_instance($post_type);
	$ppc->check_insert();

	// 主题定义的表单
	$class = 'Wndt\Module\\Wndt_' . $post_type . '_Form';
	if (class_exists($class)) {
		echo $class::build();

		// 附件编辑表单
	} elseif ('attachment' == $post_type) {
		echo Wnd\Module\Wnd_Attachment_Form::build();

		// 文章编辑表单
	} else {
		echo Wnd\Module\Wnd_Default_Post_Form::build();
	}

} catch (Exception $e) {
	echo Wnd\Module\Wnd_Module::build_error_message($e->getMessage());
}

echo '</div></main>';
get_footer();
