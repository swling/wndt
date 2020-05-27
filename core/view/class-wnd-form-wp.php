<?php
namespace Wnd\View;

use Wnd\Model\Wnd_Form_Data;

/**
 *适配本插件的ajax表单类
 *@since 2019.03.08
 *常规情况下，未选中的checkbox 和radio等字段不会出现在提交的表单数据中
 *在本环境中，为实现字段name nonce校验，未选中的字段也会发送一个空值到后台（通过 hidden字段实现），在相关数据处理上需要注意
 *为保障表单不被前端篡改，会提取所有字段的name值，结合算法生成校验码，后端通过同样的方式提取$_POST数据，并做校验
 *
 *@param bool $is_ajax_submit 是否ajax提交
 */
class Wnd_Form_WP extends Wnd_Form {

	protected $user;
	protected $filter     = null;
	protected $form_names = [];
	protected $message    = null;
	protected $is_ajax_submit;

	public static $primary_color;
	public static $second_color;

	public function __construct($is_ajax_submit = true) {
		$this->user = wp_get_current_user();

		/**
		 *@since 2019.07.17
		 *添加是否为ajax提交选项
		 */
		$this->is_ajax_submit = $is_ajax_submit;

		// 色调
		static::$primary_color = wnd_get_config('primary_color');
		static::$second_color  = wnd_get_config('second_color');

		// 继承基础变量
		parent::__construct();
	}

	public function set_filter($filter) {
		$this->filter = $filter;
	}

	/**
	 *设置提交信息
	 *@param $action 	string 		ajax中为后端处理本次提交的类名，非ajax环境中为表单接收地址
	 *@param $method 	string 		非ajax环境中为表单提交方法：POST/GET
	 */
	public function set_action($action, $method = 'POST') {
		if ($this->is_ajax_submit) {
			$this->add_hidden('action', $action);
			$this->add_hidden('_ajax_nonce', wp_create_nonce($action));
		} else {
			parent::set_action($action, $method);
		}
	}

	public function set_message($message) {
		$this->message = $message;
	}

	/**
	 *@since 2019.05.26 表单按钮默认配色
	 */
	public function set_submit_button($text, $class = '', $disabled = false) {
		$class = $class ?: 'is-' . static::$primary_color;
		$class .= $this->is_ajax_submit ? ' ajax-submit' : '';
		parent::set_submit_button($text, $class, $disabled);
	}

	/**
	 *@since 2019.05.10
	 *直接新增表单names数组元素
	 *用于nonce校验，如直接通过html方式新增的表单字段，无法被提取，需要通过这种方式新增name，以通过nonce校验
	 **/
	public function add_input_name($name) {
		$this->form_names[] = $name;
	}

	/**
	 *@since 2019.05.09
	 *未被选中的radio 与checkbox将不会发送到后端，会导致wnd_form_nonce 校验失败，此处通过设置hidden字段修改
	 */
	public function add_radio($args) {
		$this->add_hidden($args['name'], '');
		parent::add_radio($args);
	}

	public function add_checkbox($args) {
		$this->add_hidden($args['name'], '');
		parent::add_checkbox($args);
	}

	/**
	 *短信校验
	 *@param string 	$type 		  	register / reset_password / bind / verify
	 *@param string 	$template 		短信模板
	 *注册时若当前手机已注册，则无法发送验证码
	 *找回密码时若当前手机未注册，则无法发送验证码
	 **/
	public function add_sms_verify($type = 'verify', $template = '') {
		$user_phone = wnd_get_user_phone($this->user->ID);
		$this->add_html('<div class="field">');

		// 当前用户未绑定手机或更换绑定手机
		if (!$user_phone or 'bind' == $type) {
			$this->add_text(
				[
					'name'        => 'phone',
					'icon_left'   => '<i class="fa fa-phone-square"></i>',
					'required'    => true,
					'label'       => __('手机', 'wnd'),
					'placeholder' => __('手机号码', 'wnd'),
				]
			);

			// 验证当前账户手机
		} elseif ($user_phone) {
			$this->add_text(
				[
					'label'    => __('手机', 'wnd'),
					'value'    => $user_phone,
					'disabled' => true,
					'required' => true,
				]
			);
		}

		$this->add_text(
			[
				'name'        => 'auth_code',
				'icon_left'   => '<i class="fas fa-comment-alt"></i>',
				'required'    => 'required',
				'label'       => '',
				'placeholder' => __('验证码', 'wnd'),
				'addon_right' => '<button type="button" class="send-code button is-outlined is-' . static::$primary_color . '" data-type="' . $type . '" data-template="' . $template . '" data-_ajax_nonce="' . wp_create_nonce('wnd_send_code') . '" data-type_nonce="' . wp_create_nonce('sms' . $type) . '" data-is_email="0">' . __('获取验证码', 'wnd') . '</button>',
			]
		);

		$this->add_html('</div>');
	}

	/**
	 *邮箱校验
	 *@param string 	$type 		  	register / reset_password / bind / verify
	 *注册时若当前邮箱已注册，则无法发送验证码
	 *找回密码时若当前邮箱未注册，则无法发送验证码
	 **/
	public function add_email_verify($type = 'verify', $template = '') {
		$this->add_html('<div class="field">');

		// 当前用户未绑定邮箱或更换绑定邮箱
		if (!$this->user->ID or !$this->user->data->user_email or 'bind' == $type) {
			$this->add_email(
				[
					'name'        => '_user_user_email',
					'icon_left'   => '<i class="fa fa-at"></i>',
					'required'    => true,
					'label'       => __('邮箱', 'wnd'),
					'placeholder' => __('电子邮箱', 'wnd'),
				]
			);

			// 验证当前账户邮箱
		} elseif ($this->user->data->user_email) {
			$this->add_email(
				[
					'label'    => __('邮箱', 'wnd'),
					'value'    => $this->user->data->user_email,
					'disabled' => true,
					'required' => true,
				]
			);
		}

		$this->add_text(
			[
				'name'        => 'auth_code',
				'icon_left'   => '<i class="fa fa-key"></i>',
				'required'    => 'required',
				'label'       => '',
				'placeholder' => __('验证码', 'wnd'),
				'addon_right' => '<button type="button" class="send-code button is-outlined is-' . static::$primary_color . '" data-type="' . $type . '" data-template="' . $template . '" data-_ajax_nonce="' . wp_create_nonce('wnd_send_code') . '" data-type_nonce="' . wp_create_nonce('email' . $type) . '" data-is_email="1">' . __('获取验证码', 'wnd') . '</button>',
			]
		);

		$this->add_html('</div>');
	}

	// Image upload
	public function add_image_upload($args) {
		$defaults = [
			'class'          => 'upload-field',
			'label'          => 'Image upland',
			'name'           => 'wnd_file',
			'file_id'        => 0,
			'thumbnail'      => WND_URL . 'static/images/default.jpg',
			'thumbnail_size' => ['width' => $this->thumbnail_width, 'height' => $this->thumbnail_height],
			'data'           => [],
			'delete_button'  => true,
		];
		$args = array_merge($defaults, $args);

		// 合并$data
		$defaults_data = [
			'post_parent' => 0,
			'user_id'     => $this->user->ID,
			'meta_key'    => 0,
			'save_width'  => 0, //图片文件存储最大宽度 0 为不限制
			'save_height' => 0, //图片文件存储最大过度 0 为不限制
		];
		$args['data'] = array_merge($defaults_data, $args['data']);

		/**
		 *@since 2019.12.13
		 *
		 *将$args['data']数组拓展为变量
		 *$post_parent
		 *$user_id
		 *$meta_key
		 *……
		 */
		extract($args['data']);

		// 固定data
		$args['data']['is_image']         = '1';
		$args['data']['upload_nonce']     = wp_create_nonce('wnd_upload_file');
		$args['data']['delete_nonce']     = wp_create_nonce('wnd_delete_file');
		$args['data']['meta_key_nonce']   = wp_create_nonce($meta_key);
		$args['data']['thumbnail']        = $args['thumbnail'];
		$args['data']['thumbnail_width']  = $args['thumbnail_size']['width'];
		$args['data']['thumbnail_height'] = $args['thumbnail_size']['height'];
		$args['data']['method']           = $this->is_ajax_submit ? 'ajax' : $this->method;

		// 根据 meta_key 查找目标文件
		$file_id  = $args['file_id'] ?: static::get_attachment_id($meta_key, $post_parent, $user_id);
		$file_url = static::get_attachment_url($file_id, $meta_key, $post_parent, $user_id);
		$file_url = $file_url ? wnd_get_thumbnail_url($file_url, $args['thumbnail_size']['width'], $args['thumbnail_size']['height']) : '';

		$args['thumbnail'] = $file_url ?: $args['thumbnail'];
		$args['file_id']   = $file_id ?: 0;

		parent::add_image_upload($args);
	}

	// File upload
	public function add_file_upload($args) {
		$defaults = [
			'class'         => 'upload-field',
			'label'         => 'File upload',
			'name'          => 'wnd_file',
			'file_id'       => 0,
			'data'          => [],
			'delete_button' => true,
		];
		$args = array_merge($defaults, $args);

		$defaults_data = [
			'post_parent' => 0,
			'user_id'     => $this->user->ID,
			'meta_key'    => 0,
		];
		$args['data'] = array_merge($defaults_data, $args['data']);

		/**
		 *@since 2019.12.13
		 *
		 *将$args['data']数组拓展为变量
		 *
		 *$post_parent
		 *$user_id
		 *$meta_key
		 *……
		 */
		extract($args['data']);

		// 固定data
		$args['data']['upload_nonce']   = wp_create_nonce('wnd_upload_file');
		$args['data']['delete_nonce']   = wp_create_nonce('wnd_delete_file');
		$args['data']['meta_key_nonce'] = wp_create_nonce($meta_key);
		$args['data']['method']         = $this->is_ajax_submit ? 'ajax' : $this->method;

		// 根据 meta_key 查找目标文件
		$file_id  = $args['file_id'] ?: static::get_attachment_id($meta_key, $post_parent, $user_id);
		$file_url = static::get_attachment_url($file_id, $meta_key, $post_parent, $user_id);

		$args['file_id']   = $file_id ?: 0;
		$args['file_name'] = $file_url ? '<a href="' . $file_url . '" target="_blank">' . __('查看文件', 'wnd') . '</a>' : '……';

		parent::add_file_upload($args);
	}

	/**
	 *
	 *相册上传
	 *如果设置了post parent, 则上传的附件id将保留在对应的wnd_post_meta 否则保留为 wnd_user_meta
	 *meta_key: 	gallery
	 */
	public function add_gallery_upload($args) {
		$defaults = [
			'label'          => 'Gallery',
			'thumbnail_size' => ['width' => $this->thumbnail_width, 'height' => $this->thumbnail_height],
			'data'           => [],
		];
		$args = array_merge($defaults, $args);

		// 相册的meta key为固定值，不接受参数修改
		unset($args['data']['meta_key']);

		// 合并$data
		$defaults_data = [
			'post_parent' => 0,
			'user_id'     => $this->user->ID,
			'meta_key'    => 'gallery',
			'save_width'  => 0, //图片文件存储最大宽度 0 为不限制
			'save_height' => 0, //图片文件存储最大过度 0 为不限制
		];
		$args['data'] = array_merge($defaults_data, $args['data']);

		$this->build_gallery_upload($args);
	}

	// 构造表单，可设置WordPress filter 过滤表单的input_values
	public function build() {
		/**
		 *设置表单过滤filter
		 **/
		if ($this->filter) {
			$this->input_values = apply_filters($this->filter, $this->input_values);
		}

		/**
		 *@since 2019.05.09 设置表单fields校验，需要在$this->input_values filter 后执行
		 **/
		$this->build_form_nonce_field();

		/**
		 *构建表单
		 */
		parent::build();
	}

	/**
	 *@since 2019.05.09
	 *根据当前表单所有字段name生成wp nonce 用于防止用户在前端篡改表单结构提交未经允许的数据
	 */
	protected function build_form_nonce_field() {
		// 提取表单字段names
		foreach ($this->get_input_values() as $input_value) {
			if (!isset($input_value['name'])) {
				continue;
			}

			if (isset($input_value['disabled']) and $input_value['disabled']) {
				continue;
			}

			// 可能为多选字段：需要移除'[]'
			$this->form_names[] = str_replace('[]', '', $input_value['name']);
		}
		unset($input_value);

		// 根据表单字段生成wp nonce并加入表单字段
		$this->add_html(Wnd_Form_Data::build_form_nonce_field($this->form_names));
	}

	/**
	 *构建表单头部
	 */
	protected function build_form_header() {
		/**
		 *@since 2019.07.17 ajax表单
		 */
		if ($this->is_ajax_submit) {
			$this->add_form_attr('action', '');
			$this->add_form_attr('method', 'POST');
			$this->add_form_attr('onsubmit', 'return false');
		}
		parent::build_form_header();

		$this->html .= '<div class="ajax-message">' . $this->message . '</div>';
	}

	// 构建相册上传
	protected function build_gallery_upload($args) {
		/**
		 *@since 2019.12.13
		 *
		 *将$args['data']数组拓展为变量
		 *
		 *$post_parent
		 *$user_id
		 *$meta_key
		 *……
		 */
		extract($args['data']);

		// 固定data
		$args['data']['upload_nonce']     = wp_create_nonce('wnd_upload_file');
		$args['data']['delete_nonce']     = wp_create_nonce('wnd_delete_file');
		$args['data']['meta_key_nonce']   = wp_create_nonce($meta_key);
		$args['data']['thumbnail_width']  = $args['thumbnail_size']['width'];
		$args['data']['thumbnail_height'] = $args['thumbnail_size']['height'];
		$args['data']['method']           = $this->is_ajax_submit ? 'ajax' : $this->method;

		// 根据user type 查找目标文件
		$images = $post_parent ? wnd_get_post_meta($post_parent, $meta_key) : wnd_get_user_meta($user_id, $meta_key);
		$images = is_array($images) ? $images : [];

		/**
		 *@since 2019.05.06 构建 html
		 */
		$id   = 'gallery-' . $this->id;
		$data = ' data-id="' . $id . '"';
		foreach ($args['data'] as $key => $value) {
			$data .= ' data-' . $key . '="' . $value . '" ';
		}unset($key, $value);

		$html = '<div id="' . $id . '" class="field upload-field">';
		$html .= '<div class="field"><div class="ajax-message"></div></div>';

		// 上传区域
		$html .= '<div class="field">';
		$html .= '<div class="file">';
		$html .= '<label class="file-label">';
		$html .= '<input type="file" multiple="multiple" class="file-input" name="wnd_file[]' . '"' . $data . 'accept="image/*" >';
		$html .= ' <span class="file-cta"><span class="file-icon"><i class="fas fa-upload"></i></span><span class="file-label">选择图片</span></span>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';

		// 遍历输出图片集
		$html .= '<div class="gallery columns is-vcentered has-text-centered is-multiline">';
		if (!$images) {
			$html .= '<div class="column default-message">';
			$html .= '<p>' . $args['label'] . '</p>';
			$html .= '</div>';
		}
		foreach ($images as $key => $attachment_id) {
			$attachment_url = wp_get_attachment_url($attachment_id);
			$thumbnail_url  = wnd_get_thumbnail_url($attachment_url, $args['thumbnail_size']['width'], $args['thumbnail_size']['height']);
			if (!$attachment_url) {
				unset($images[$key]); // 在字段数据中取消已经被删除的图片
				continue;
			}

			$html .= '<div class="attachment-' . $attachment_id . ' column is-narrow">';
			$html .= '<a><img class="thumbnail" src="' . $thumbnail_url . '" data-url="' . $attachment_url . '" height="' . $args['thumbnail_size']['height'] . '" width="' . $args['thumbnail_size']['width'] . '"></a>';
			$html .= '<a class="delete" data-id="' . $id . '" data-file_id="' . $attachment_id . '"></a>';
			$html .= '</div>';
		}
		unset($key, $attachment_id);
		wnd_update_post_meta($post_parent, $meta_key, $images); // 若字段中存在被删除的图片数据，此处更新
		$html .= '</div>';
		$html .= '</div>';

		$this->add_html($html);
	}

	/**
	 *@since 2020.04.13
	 *
	 *根据meta key获取附件ID
	 */
	protected static function get_attachment_id($meta_key, $post_parent, $user_id) {
		// option
		if (0 === stripos($meta_key, '_option_')) {
			$option = str_replace('_option_', '', $meta_key);
			return get_option($option);
		}

		// post meta
		if ($post_parent) {
			return wnd_get_post_meta($post_parent, $meta_key);
		}

		// user meta
		return wnd_get_user_meta($user_id, $meta_key);
	}

	/**
	 *@since 2020.04.13
	 *
	 *获取附件URL
	 *如果字段存在，但文件已不存在，例如已被后台删除，删除对应meta_key or option
	 */
	protected static function get_attachment_url($attachment_id, $meta_key, $post_parent, $user_id) {
		$attachment_url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;

		if ($attachment_id and !$attachment_url) {
			if (0 === stripos($meta_key, '_option_')) {
				$option = str_replace('_option_', '', $meta_key);
				delete_option($option);
			} elseif ($post_parent) {
				wnd_delete_post_meta($post_parent, $meta_key);
			} else {
				wnd_delete_user_meta($user_id, $meta_key);
			}
		}

		return $attachment_url;
	}
}
