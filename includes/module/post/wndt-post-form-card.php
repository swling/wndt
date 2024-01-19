<?php

namespace Wndt\Module\Post;

use Wnd\Module\Post\Wnd_Post_Form;
use Wnd\View\Wnd_Form_Post;

/**
 * @since 2019.01.31 产品表单
 */
class Wndt_Post_Form_Card extends Wnd_Post_Form {

	public static $post_type = 'card';

	protected static function configure_form(array $args = []): object {
		$defaults = [
			'post_id'     => 0,
			'post_parent' => 0,
		];
		$args = wp_parse_args($args, $defaults);

		$post_id     = (int) $args['post_id'];
		$post_parent = (int) $args['post_parent'];

		/**
		 * @since 2019.03.11 表单类
		 */
		$form = new Wnd_Form_Post('card', $post_id);
		$form->set_post_parent($post_parent);
		$form->add_post_title();
		$form->add_post_content(true);
		$form->add_post_excerpt(__('摘要', 'wnd'));

		// 标签
		$form->add_post_tags('card_tag');
		$form->add_post_price();
		$form->add_html('
<div class="field is-horizontal">
<div class="field-label is-normal"><label class="label is-hidden-mobile">卡券设置</label></div>
<div class="field-body"><div class="field">' . wnd_modal_button('卡券设置', 'wndt_keys_form', ['post_id' => $form->get_post()->ID ?? 0]) . '</div></div>
</div>');

		// 分类
		$form->add_post_term_select(['taxonomy' => 'card_cat']);

		// 缩略图
		$form->set_thumbnail_size(100, 100);
		$form->add_post_thumbnail(400, 300, '缩略图');
		$form->add_post_status_select();
		$form->set_submit_button(__('保存', 'wnd'));
		// 以当前函数名设置filter hook
		$form->set_filter(__CLASS__);

		return $form;
	}
}
