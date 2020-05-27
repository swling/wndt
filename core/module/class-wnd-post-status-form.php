<?php
namespace Wnd\Module;

use Wnd\View\Wnd_Form_WP;

/**
 *@since 2019.01.20
 *快速编辑文章状态表单
 */
class Wnd_Post_Status_Form extends Wnd_Module {

	public static function build($post_id = 0) {
		$post = get_post($post_id);
		if (!$post) {
			return __('ID无效', 'wnd');
		}

		switch ($post->post_status) {

		case 'publish':
			$status_text = __('已发布', 'wnd');
			break;

		case 'pending':
			$status_text = __('待审核', 'wnd');
			break;

		case 'draft':
			$status_text = __('草稿', 'wnd');
			break;

		case false:
			$status_text = __('已删除', 'wnd');
			break;

		default:
			$status_text = $post->post_status;
			break;
		}

		$form = new Wnd_Form_WP();
		$form->add_html('<div class="field is-grouped is-grouped-centered">');
		$form->add_radio(
			[
				'name'     => 'post_status',
				'options'  => [
					__('发布', 'wnd') => 'publish',
					__('待审', 'wnd') => 'pending',
					__('关闭', 'wnd') => 'close',
					__('草稿', 'wnd') => 'draft',
					__('删除', 'wnd') => 'delete',
				],
				'required' => 'required',
				'checked'  => $post->post_status,
				'class'    => 'is-checkradio is-danger',
			]
		);
		$form->add_html('</div>');

		// 管理员权限
		if (wnd_is_manager()) {
			// 公开的post type可设置置顶
			if (in_array($post->post_type, get_post_types(['public' => true]))) {
				$form->add_html('<div class="field is-grouped is-grouped-centered">');
				$form->add_radio(
					[
						'name'    => 'stick_post',
						'options' => [
							__('置顶', 'wnd') => 'stick',
							__('取消', 'wnd') => 'unstick',
						],
						'checked' => (array_search($post->ID, wnd_get_sticky_posts($post->post_type)) === false) ? '' : 'stick',
						'class'   => 'is-checkradio is-danger',
					]
				);
				$form->add_html('</div>');
			}

			$form->add_textarea(
				[
					'name'        => 'remarks',
					'placeholder' => __('备注（可选）', 'wnd'),
				]
			);
		}

		if ($post->post_type == 'order') {
			$form->add_html(wnd_message(__('删除订单记录，不可退款，请谨慎操作', 'wnd'), 'is-danger'));
		}

		$form->add_hidden('post_id', $post_id);
		$form->set_action('wnd_update_post_status');
		$form->add_form_attr('id', 'post-status');
		$form->set_submit_button(__('提交', 'wnd'));
		$form->build();

		return $form->html;
	}
}
