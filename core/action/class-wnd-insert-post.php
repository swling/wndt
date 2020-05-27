<?php
namespace Wnd\Action;

use Exception;
use Wnd\Model\Wnd_Form_Data;
use Wnd\Model\Wnd_Post;

class Wnd_Insert_Post extends Wnd_Action_Ajax {

	protected static $post_data;

	protected static $meta_data;

	protected static $wp_meta_data;

	protected static $terms_data;

	protected static $post_id;

	protected static $update_post;

	/**
	 *@see README.md
	 *ajax post $_POST name规则：
	 *post field：_post_{field}
	 *post meta：
	 *_meta_{key} (*自定义数组字段)
	 *_wpmeta_{key} (*WordPress原生字段)
	 *_term_{taxonomy}(*taxonomy)
	 *
	 *@since 初始化
	 *
	 *保存提交数据
	 *@param 	array	$_POST 				全局表单数据
	 *@param 	bool 	$verify_form_nonce  是否校验表单数据来源
	 *
	 *@return 	array 						操作结果
	 *
	 */
	public static function execute($verify_form_nonce = true): array{
		try {
			static::parse_data($verify_form_nonce);
			static::check();
			static::insert();
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}

		// 完成返回
		$permalink    = get_permalink(static::$post_id);
		$redirect_to  = $_REQUEST['redirect_to'] ?? '';
		$status       = $redirect_to ? 3 : 2;
		$return_array = [
			'status' => $status,
			'msg'    => __('发布成功', 'wnd'),
			'data'   => [
				'id'          => static::$post_id,
				'url'         => $permalink,
				'redirect_to' => $redirect_to,
			],
		];

		return apply_filters('wnd_insert_post_return', $return_array, static::$post_data['post_type'], static::$post_id);
	}

	/**
	 *解析提交数据
	 */
	protected static function parse_data($verify_form_nonce) {
		// 实例化当前提交的表单数据
		try {
			$form_data            = new Wnd_Form_Data($verify_form_nonce);
			static::$post_data    = $form_data->get_post_data();
			static::$meta_data    = $form_data->get_post_meta_data();
			static::$wp_meta_data = $form_data->get_wp_post_meta_data();
			static::$terms_data   = $form_data->get_terms_data();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		// 指定ID则为更新
		static::$post_id     = static::$post_data['ID'] ?? 0;
		static::$update_post = static::$post_id ? get_post(static::$post_id) : false;

		/**
		 *文章特定字段处理：
		 *
		 *1.Post一旦创建，不允许再次修改post type
		 *
		 *2.若未指定post name（slug）：已创建的Post保持原有，否则为随机码
		 *
		 *3.filter：post status
		 */
		static::$post_data['post_type']   = static::$post_id ? static::$update_post->post_type : (static::$post_data['post_type'] ?? 'post');
		static::$post_data['post_name']   = (static::$post_data['post_name'] ?? false) ?: (static::$post_id ? static::$update_post->post_name : uniqid());
		static::$post_data['post_status'] = apply_filters('wnd_insert_post_status', 'pending', static::$post_data['post_type'], static::$post_id);
	}

	/**
	 *更新权限判断
	 */
	protected static function check() {
		if (static::$post_id) {
			if (!static::$update_post) {
				throw new Exception(__('ID无效', 'wnd'));
			}

			if (!current_user_can('edit_post', static::$post_id)) {
				throw new Exception(__('权限错误', 'wnd'));
			}
		}

		/**
		 *@since 2019.07.17
		 *attachment仅允许更新，而不能直接写入（写入应在文件上传时完成）
		 */
		if ('attachment' == static::$post_data['post_type']) {
			throw new Exception(__('未指定文件', 'wnd'));
		}

		/**
		 *限制ajax可以创建的post类型，避免功能型post被意外创建
		 *功能型post应通常具有更复杂的权限控制，并wp_insert_post创建
		 *
		 */
		if (!in_array(static::$post_data['post_type'], Wnd_Post::get_allowed_post_types())) {
			throw new Exception(__('类型无效', 'wnd'));
		}

		// 写入及更新权限过滤
		$can_insert_post = apply_filters('wnd_can_insert_post', ['status' => 1, 'msg' => ''], static::$post_data['post_type'], static::$post_id);
		if ($can_insert_post['status'] === 0) {
			throw new Exception($can_insert_post['msg']);
		}
	}

	/**
	 *写入数据
	 */
	protected static function insert() {
		// 创建revision 该revision不同于WordPress原生revision：创建一个同类型Post，设置post parent，并设置wp post meta
		if (static::should_be_update_reversion()) {
			static::$post_data['ID']                                 = Wnd_Post::get_revision_id(static::$post_id);
			static::$post_data['post_parent']                        = static::$post_id;
			static::$post_data['post_name']                          = uniqid();
			static::$wp_meta_data[Wnd_Post::get_revision_meta_key()] = 'true';
		}

		// 创建或更新Post
		if (static::$post_data['ID']) {
			static::$post_id = wp_update_post(static::$post_data);
		} else {
			static::$post_id = wp_insert_post(static::$post_data);
		}

		if (!static::$post_id) {
			throw new Exception(__('写入数据失败', 'wnd'));
		}

		if (is_wp_error(static::$post_id)) {
			throw new Exception(static::$post_id->get_error_message());
		}

		/**
		 *设置Meta
		 *
		 */
		Wnd_Post::set_meta(static::$post_id, static::$meta_data, static::$wp_meta_data);

		/**
		 *设置Terms
		 *
		 */
		Wnd_Post::set_terms(static::$post_id, static::$terms_data);
	}

	/**
	 *判断是否应该创建一个版本
	 *@since 2020.05.20
	 */
	protected static function should_be_update_reversion(): bool {
		// 当前编辑即为revision无需新建
		if (Wnd_Post::is_revision(static::$post_id)) {
			return false;
		}

		// 普通用户，已公开发布的内容再次编辑，需要创建revision
		if (!wnd_is_manager() and 'publish' == static::$update_post->post_status) {
			return true;
		}

		return false;
	}
}
