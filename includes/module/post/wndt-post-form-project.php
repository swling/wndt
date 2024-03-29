<?php
namespace Wndt\Module\Post;

use Wnd\Module\Post\Wnd_Post_Form;
use Wnd\View\Wnd_Form_Post;

/**
 * @since 2019.01.31 项目内容表单
 */
class Wndt_Post_Form_Project extends Wnd_Post_Form {

	public static $post_type = 'project';

	protected static function configure_form(array $args = []): object{
		$defaults = [
			'post_id'     => 0,
			'post_parent' => 0,
			'is_free'     => false,
		];
		$args = wp_parse_args($args, $defaults);

		$post_id     = (int) $args['post_id'];
		$post_parent = (int) $args['post_parent'];
		$is_free     = (bool) $args['is_free'];

		/**
		 * @since 2019.03.11 表单类
		 */
		$form = new Wnd_Form_Post('project', $post_id);
		$form->set_post_parent($post_parent);
		$form->add_html('<div class="columns post-form">');
		$form->add_html('<div class="column">');
		$form->add_post_title('');
		$form->add_post_excerpt();

		// 标签
		// $form->add_post_tags('project_tag');
		// $form->add_html(wnd_notification('请用回车键区分多个标签', 'is-primary'));

		if (!$is_free) {
			$form->add_post_paid_file_upload();
		}

		/**
		 * @since 2019.04 富媒体编辑器仅在非ajax请求中有效
		 */
		$form->add_html(wnd_notification('设置价格后，使用more标签（Shift+Alt+T），区分免费内容与付费内容', 'is-primary'));
		$form->add_post_content(true);
		$form->add_post_status_select();
		$form->add_html('</div>');

		$form->add_html('<div class="column is-3">');
		// 分类
		$form->add_post_term_select(['taxonomy' => 'project_cat']);

		// 版本号
		$form->add_post_meta('version', 'Version', '版本号', true);

		// 缩略图
		$form->set_thumbnail_size(200, 150);
		$form->add_post_thumbnail(400, 300, '缩略图');
		$form->add_html('</div>');

		$form->add_html('</div>');

		$form->set_submit_button(__('保存', 'wnd'));
		// 以当前函数名设置filter hook
		$form->set_filter(__CLASS__);

		return $form;
	}
}
