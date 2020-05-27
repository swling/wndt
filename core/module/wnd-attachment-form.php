<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_Post;
use Wnd\View\Wnd_Form_WP;

/**
 *@since 2019.07.16
 *上传或编辑附件信息
 *指定$args['attachment_id'] 表示为编辑
 *
 *原理：
 *基于 post parent创建文件上传字段，ajax上传附件并附属到指定post parent
 *attachment post在上传文件后，由WordPress创建
 *后端将附件文件attachment post信息返回
 *@see php: Wnd\Action\Wnd_Upload_File
 *
 *创建父级文件上传字段的同时，创建空白的attachment post form（实际表单是通过这两个表单的字段重新形成）
 *利用JavaScript捕获上传文件后返回的attachment post信息
 *JavaScript捕获新上传的attachment post信息后，首先判断当前表单对应字段是否已有信息，若有值，则不作修改。ID除外。
 *完成对表单字段信息的动态替换后，自动提交一次
 *若需修改信息，则编辑对应字段，手动提交一次
 *@see JavaScript: $("body").on("change", "[type='file']", function() {});
 *
 *文件替换：
 *指定attachment_id，并调用本函数，为防止上传附件后忘记删除原有文件（操作直观上，这是一次替换），此时文件字段为禁用状态
 *删除原有文件后，前端恢复上传
 *选择新的文件，则重复上述ajax文件上传过程，即此时表单已经动态更改为编辑最新上传的attachment post
 *通过保留相同的post_name(别名)、及menu_order（排序）可实现用户端的无缝替换文件。
 *本质上，替换文件，是删除后的新建，是全新的attachment post
 *
 */
class Wnd_Attachment_Form extends Wnd_Module {

	public static function build($args = []) {
		$defaults = [
			'attachment_id' => 0,
			'post_parent'   => 0,
			'meta_key'      => null,
		];
		$args = wp_parse_args($args, $defaults);

		$attachment_id = $args['attachment_id'];
		$post_parent   = $attachment_id ? get_post($attachment_id)->post_parent : $args['post_parent'];

		/**
		 * 构建父级表单字段，以供文件ajax上传归属到父级post
		 */
		$parent_post_form = new Wnd_Form_WP();

		// 文件上传字段可能被前端设置disabled属性，导致无法通过表单一致性校验，故此设置同名固定隐藏字段
		$parent_post_form->add_hidden('wnd_file', '');
		$parent_post_form->add_file_upload(
			[
				'label'    => __('附件上传', 'wnd'),
				'disabled' => $attachment_id ? 'disabled' : false,
				'file_id'  => $attachment_id,

				/**
				 *如果设置了meta_key及post parent, 则上传的附件id将保留在对应的wnd_post_meta
				 *若仅设置了meta_key否则保留为 wnd_user_meta
				 *若未设置meta_key、则不在meta中保留附件信息，仅能通过指定id方式查询
				 */
				'data'     => [
					'meta_key'    => $args['meta_key'],
					'post_parent' => $post_parent,
				],
			]
		);

		/**
		 *上传媒体信息表单字段。attachment 无法也不应创建草稿
		 *此处的attachment post_ID将根据上传文件后，ajax返回值获取
		 */
		$attachment_post_form = new Wnd_Form_Post('attachment', $attachment_id, false);
		if ($attachment_id) {
			$attachment_post_form->set_message(wnd_message(__('如需更改文件，请先删除后重新选择文件', 'wnd'), Wnd_Form_WP::$second_color));
		}
		$attachment_post_form->add_post_title(__('文件名称', 'wnd'));
		$attachment_post_form->add_html('<div class="field is-horizontal"><div class="field-body">');
		$attachment_post_form->add_post_menu_order(__('排序', 'wnd'), __('输入排序', 'wnd'));
		$attachment_post_form->add_text(
			[
				'label'    => '文件ID',
				'name'     => '_post_ID',
				'value'    => $attachment_id,
				'disabled' => true,
			]
		);
		$attachment_post_form->add_html('</div></div>');
		$attachment_post_form->add_post_name(__('链接别名', 'wnd'), __('附件的固定链接别名', 'wnd'));
		$attachment_post_form->add_post_content(true, __('简介', 'wnd'), true);
		$attachment_post_form->set_submit_button(__('保存', 'wnd'));

		// 将上述两个表单字段，合并组成一个表单字段
		$input_values = array_merge($parent_post_form->get_input_values(), $attachment_post_form->get_input_values());
		$attachment_post_form->set_input_values($input_values);
		$attachment_post_form->build();

		return $attachment_post_form->html;
	}
}
