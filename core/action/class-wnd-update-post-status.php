<?php
namespace Wnd\Action;

use Wnd\Model\Wnd_Post;

class Wnd_Update_Post_Status extends Wnd_Action_Ajax {

	static $post_id;
	static $after_status;
	static $remarks;
	static $stick_post;
	static $before_post;

	/**
	 *@since 2019.01.21
	 *@param  $_POST['post_id']
	 *@param  $_POST['post_status']
	 *@return array
	 *前端快速更改文章状态
	 *依赖：wp_update_post、wp_delete_post
	 */
	public static function execute(): array{
		// 获取数据
		static::$post_id      = (int) $_POST['post_id'];
		static::$after_status = $_POST['post_status'];
		static::$remarks      = $_POST['remarks'] ?? '';
		static::$stick_post   = $_POST['stick_post'] ?? '';
		static::$before_post  = get_post(static::$post_id);

		if (!static::$before_post) {
			return ['status' => 0, 'msg' => __('无效的Post', 'wnd')];
		}

		// 在现有注册的post status基础上新增 delete，该状态表示直接删除文章 @since 2019.03.03
		if (!in_array(static::$after_status, array_merge(get_post_stati(), ['delete']))) {
			return ['status' => 0, 'msg' => __('无效的Post状态', 'wnd')];
		}

		// 权限检测
		$can_array              = ['status' => current_user_can('edit_post', static::$post_id) ? 1 : 0, 'msg' => __('权限错误', 'wnd')];
		$can_update_post_status = apply_filters('wnd_can_update_post_status', $can_array, static::$before_post, static::$after_status);
		if ($can_update_post_status['status'] === 0) {
			return $can_update_post_status;
		}

		// 更新Post
		if ('delete' == static::$after_status) {
			return static::delete_post();
		} else {
			return static::update_status();
		}
	}

	/**
	 *更新状态
	 */
	protected static function update_status() {
		//执行更新：如果当前post为自定义版本，将版本数据更新到原post
		if ('publish' == static::$after_status and Wnd_Post::is_revision(static::$post_id)) {
			$update = Wnd_Post::restore_post_revision(static::$post_id, static::$after_status);
		} else {
			$update = wp_update_post(['ID' => static::$post_id, 'post_status' => static::$after_status]);
		}

		/**
		 *@since 2019.06.11 置顶操作
		 */
		static::stick_post();

		// 站内信
		static::send_mail();

		// 完成更新
		if ($update) {
			return ['status' => 4, 'msg' => __('更新成功', 'wnd')];
		} else {
			return ['status' => 0, 'msg' => __('写入数据失败', 'wnd')];
		}
	}

	/**
	 *删除文章 无论是否设置了$force_delete 自定义类型的文章都会直接被删除
	 */
	protected static function delete_post() {
		$delete = wp_delete_post(static::$post_id, true);
		if ($delete) {
			static::send_mail();

			return ['status' => 5, 'msg' => __('已删除', 'wnd')];
		} else {
			return ['status' => 0, 'msg' => __('操作失败', 'wnd')];
		}
	}

	/**
	 *@since 2019.06.11 置顶操作
	 */
	protected static function stick_post() {
		if (wnd_is_manager()) {
			return;
		}

		if ('stick' == static::$stick_post and 'publish' == static::$after_status) {
			wnd_stick_post(static::$post_id);

		} elseif ('unstick' == static::$stick_post) {
			wnd_unstick_post(static::$post_id);
		}
	}

	/**
	 *@since 2020.05.23
	 *站内信
	 */
	protected static function send_mail() {
		if (get_current_user_id() == static::$before_post->post_author) {
			return false;
		}

		if ('pending' == static::$before_post->post_status and 'draft' == static::$after_status) {
			$subject = __('内容审核失败', 'wnd') . '[ID' . static::$post_id . ']';
			$content = wnd_message(static::$remarks . '<p><a href="' . get_permalink(static::$post_id) . '" target="_blank">查看</a></p>', 'is-danger');
		} elseif ('delete' == static::$after_status) {
			$subject = __('内容已被删除', 'wnd') . '[ID' . static::$post_id . ']';
			$content = wnd_message('<p>Title:《' . static::$before_post->post_title . '》</p>' . static::$remarks, 'is-danger');
		} else {
			return false;
		}

		return wnd_mail(static::$before_post->post_author, $subject, $content);
	}
}
