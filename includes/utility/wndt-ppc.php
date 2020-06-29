<?php
namespace Wndt\Utility;

use Exception;

/**
 *@since 2019.09.25
 *文章发布管理权限：Post permission control
 */
class Wndt_PPC {

	protected $user_id;
	protected $profile_id;
	protected $user_role;
	protected $user_attribute;

	protected static $post_type;
	protected $post;
	protected $post_id;
	protected $post_status;
	protected $post_title;

	public function __construct() {
		$this->user_id = get_current_user_id();
	}

	/**
	 *根据post type 实例化
	 */
	public static function get_instance($post_type) {
		if (!$post_type) {
			throw new Exception($post_type . '未设指定Post Type');
		}
		static::$post_type = $post_type;

		$class = __NAMESPACE__ . '\Wndt_PPC_' . $post_type;
		if (is_callable([$class, 'get_instance'])) {
			return new $class;
		} else {
			return new self();
		}
	}

	/**
	 *设定Post ID
	 */
	public function set_post_id(int $post_id) {
		$this->post_id = $post_id;
		$this->post    = $this->post_id ? get_post($this->post_id) : false;
		if (!$this->post) {
			throw new Exception('指定ID无效');
		}

		if (static::$post_type != $this->post->post_type) {
			throw new Exception('指定ID Post Type与实例化Post Type不一致');
		}
	}

	/**
	 *设定Post Status
	 */
	public function set_post_status($post_status) {
		$this->post_status = $post_status;
	}

	/**
	 *设定Post Title
	 */
	public function set_post_title($post_title) {
		$this->post_title = $post_title;
	}

	/**
	 *基础写入权限检查：登录
	 */
	public function check_insert() {
		if (!$this->user_id) {
			throw new Exception('请登录');
		}
	}

	/**
	 *@since 2018
	 *基础更新文章权限检测
	 */
	public function check_update() {
		if (!$this->post) {
			throw new Exception('获取内容失败');
		}

		// 更新权限
		if (!current_user_can('edit_post', $this->post_id)) {
			throw new Exception('权限错误');
		}
	}

	/**
	 *@since 2019.01.22
	 *基础更新文章状态权限：非管理员不等直接发布公开
	 *
	 *@param $this->post_status
	 *@param $this->post_id
	 */
	public function check_status_update() {
		if (wnd_is_manager()) {
			return true;
		}

		if ($this->post_status == 'publish') {
			throw new Exception('权限错误');
		}
	}

	/**
	 *文件上传检测
	 **/
	public function check_file_upload($post_parent, $meta_key) {
		if ($meta_key != 'gallery' or !$post_parent) {
			return true;
		}

		// 限制产品相册上传数量
		$old_images            = wnd_get_post_meta($post_parent, 'gallery');
		$old_images_count      = is_array($old_images) ? count($old_images) : 0;
		$current_upload_count  = count($_FILES["wnd_file"]['name']);
		$gallery_picture_limit = (int) wndt_get_config('gallery_picture_limit');

		if ($old_images_count + $current_upload_count > $gallery_picture_limit) {
			throw new Exception('最多上传' . $gallery_picture_limit . '张图片');
		}
	}
}
