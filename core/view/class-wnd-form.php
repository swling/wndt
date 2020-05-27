<?php
namespace Wnd\View;

/**
 *Class for creating dynamic Bulma forms.
 *@since 2019.03
 *Base on form-builder @link https://github.com/swling/form-builder
 *@link https://wndwp.com
 *@author swling tangfou@gmail.com
 *这是一个独立的php表单生成器，可于任何PHP环境中使用
 */
class Wnd_Form {

	protected $id;

	protected $form_attr = [];

	protected $form_title;

	protected $is_title_centered = false;

	protected $input_values = [];

	protected $with_upload;

	protected $submit_text = 'Submit';

	protected $submit_class;

	protected $submit_disabled;

	protected $action;

	protected $method;

	protected $thumbnail_width = 100;

	protected $thumbnail_height = 100;

	public $html;

	protected static $defaults = [
		'id'          => null,
		'class'       => null,
		'name'        => '',
		'value'       => '',
		'label'       => null,
		'options'     => [], //value of select/radio. Example: [label=>value]
		'checked'     => null, // checked value of select/radio; bool of checkbox
		'selected'    => null, // selected value if select
		'required'    => false,
		'disabled'    => false,
		'autofocus'   => false,
		'readonly'    => false,
		'placeholder' => '',
		'size'        => '',
		'maxlength'   => '',
		'min'         => '',
		'max'         => '',
		'step'        => '',
		'pattern'     => '',
		'multiple'    => '',

		// icon and addon
		'icon_left'   => null,
		'icon_right'  => null,
		'addon_left'  => null,
		'addon_right' => null,
	];

	/**
	 *@since 2020.04.17
	 *input字段类型
	 */
	protected static $input_types = [
		'text',
		'number',
		'email',
		'password',
		'url',
		'color',
		'date',
		'range',
		'tel',
	];

	// 初始化构建
	public function __construct() {
		$this->id = uniqid();
	}

	/**
	 *@since 2019.03.10 设置表单属性
	 */
	public function set_form_title($form_title, $is_title_centered = false) {
		$this->form_title        = $form_title;
		$this->is_title_centered = $is_title_centered;
	}

	/**
	 *设置表单缩略图尺寸
	 *@param int 	$width
	 *@param int 	$height
	 */
	public function set_thumbnail_size($width, $height) {
		$this->thumbnail_width  = $width;
		$this->thumbnail_height = $height;
	}

	// Submit
	public function set_submit_button($text, $class = '', $disabled = false) {
		$this->submit_text     = $text;
		$this->submit_class    = $class;
		$this->submit_disabled = $disabled;
	}

	// action
	public function set_action($action, $method) {
		$this->method = $method;
		$this->action = $action;
	}

	// 直接设置当前表单的组成数组（通常用于配合 filter 过滤）
	public function set_input_values($input_values) {
		$this->input_values = $input_values;
	}

	/**
	 *@since 2019.08.29
	 *设置表单属性
	 **/
	public function add_form_attr($key, $value) {
		$this->form_attr[$key] = $value;
	}

	/**
	 *@since 2019.03.10 设置常规input 字段
	 */
	// text
	public function add_text($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'text';
		$this->input_values[] = $args;
	}

	// number
	public function add_number($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'number';
		$this->input_values[] = $args;
	}

	// hidden
	public function add_hidden($name, $value) {
		$this->input_values[] = [
			'type'  => 'hidden',
			'name'  => $name,
			'value' => $value,
		];
	}

	// textarea
	public function add_textarea($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'textarea';
		$this->input_values[] = $args;
	}

	// email
	public function add_email($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'email';
		$this->input_values[] = $args;
	}

	// password
	public function add_password($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'password';
		$this->input_values[] = $args;
	}

	/**
	 *@since 2019.08.23
	 *新增HTML5 字段
	 */
	// URL
	public function add_url($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'url';
		$this->input_values[] = $args;
	}

	// color
	public function add_color($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'color';
		$this->input_values[] = $args;
	}

	// date
	public function add_date($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'date';
		$this->input_values[] = $args;
	}

	// range
	public function add_range($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'range';
		$this->input_values[] = $args;
	}

	// tel
	public function add_tel($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'tel';
		$this->input_values[] = $args;
	}

	// select
	public function add_select($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'select';
		$this->input_values[] = $args;
	}

	// radio
	public function add_radio($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'radio';
		$this->input_values[] = $args;
	}

	// checkbox
	public function add_checkbox($args) {
		$args                 = array_merge(static::$defaults, $args);
		$args['type']         = 'checkbox';
		$this->input_values[] = $args;
	}

	// Image upload
	public function add_image_upload($args) {
		$defaults = [
			'id'             => 'image-upload-' . $this->id,
			'thumbnail'      => '',
			'thumbnail_size' => ['width' => $this->thumbnail_width, 'height' => $this->thumbnail_height],
			'file_id'        => 0,
			'data'           => [],
			'delete_button'  => true,
		];
		$args                 = array_merge(static::$defaults, $defaults, $args);
		$args['type']         = 'image_upload';
		$this->input_values[] = $args;

		if (!$this->with_upload) {
			$this->with_upload = true;
		}
	}

	// File upload
	public function add_file_upload($args) {
		$defaults = [
			'id'            => 'file-upload-' . $this->id,
			'file_name'     => 'file name',
			'file_id'       => 0,
			'data'          => [],
			'delete_button' => true,
		];
		$args                 = array_merge(static::$defaults, $defaults, $args);
		$args['type']         = 'file_upload';
		$this->input_values[] = $args;

		if (!$this->with_upload) {
			$this->with_upload = true;
		}
	}

	/**
	 *@since 2019.03.06 在表单当前位置插入指定html代码以补充现有方法无法实现的效果
	 */
	public function add_html($html) {
		$this->input_values[] = [
			'type'  => 'html',
			'value' => $html,
		];
	}

	/**
	 *@since 2019.03.06
	 *表单构造函数
	 **/
	public function build() {
		$this->build_form_header();
		$this->build_input_values();
		$this->build_submit_button();
		$this->build_form_footer();
	}

	protected function build_form_header() {
		$this->add_form_attr('id', $this->form_attr['id'] ?? $this->id);

		if (!is_null($this->method)) {
			$this->add_form_attr('method', $this->method);
		}

		if (!is_null($this->action)) {
			$this->add_form_attr('action', $this->action);
		}

		if ($this->with_upload) {
			$this->add_form_attr('enctype', 'multipart/form-data');
		}

		$html = '<form' . $this->build_form_attr() . '>';

		if ($this->form_title) {
			$html .= $this->is_title_centered ? '<div class="field content has-text-centered">' : '<div class="field content">';
			$html .= '<h3>' . $this->form_title . '</h3>';
			$html .= '</div>';
		}

		$this->html = $html;
	}

	protected function build_input_values() {
		$html = '';
		foreach ($this->input_values as $input_key => $input_value) {
			// input
			if (in_array($input_value['type'], static::$input_types)) {
				$html .= $this->build_input($input_value, $input_key);
				continue;
			}

			switch ($input_value['type']) {
			case 'hidden':
				$html .= $this->build_hidden($input_value, $input_key);
				break;
			case 'radio':
				$html .= $this->build_radio($input_value, $input_key);
				break;
			case 'checkbox':
				$html .= $this->build_checkbox($input_value, $input_key);
				break;
			case 'select':
				$html .= $this->build_select($input_value, $input_key);
				break;
			case 'image_upload':
				$html .= $this->build_image_upload($input_value, $input_key);
				break;
			case 'file_upload':
				$html .= $this->build_file_upload($input_value, $input_key);
				break;
			case 'textarea':
				$html .= $this->build_textarea($input_value, $input_key);
				break;
			case 'html':
				$html .= $this->build_html($input_value, $input_key);
				break;
			default:
				break;
			}
		}
		unset($input_value);

		$this->html .= $html;
		return $html;
	}

	protected function build_select($input_value, $input_key) {
		$html = '<div class="field">';
		$html .= static::build_label($input_value);
		$html .= '<div class="control">';
		$html .= '<div class="select">';
		$html .= '<select' . static::build_input_id($input_value) . static::build_input_attr($input_value) . '>';
		foreach ($input_value['options'] as $key => $value) {
			if (is_array($input_value['selected'])) {
				$checked = in_array($value, $input_value['selected']) ? ' selected="selected"' : '';
			} else {
				$checked = ($input_value['selected'] === $value) ? ' selected="selected"' : '';
			}

			$html .= '<option value="' . $value . '"' . $checked . '>' . $key . '</option>';
		}unset($key, $value);
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	protected function build_radio($input_value, $input_key) {
		$html = '<div' . static::build_input_id($input_value) . ' class="field' . static::get_class($input_value, true) . '">';
		$html .= static::build_label($input_value);
		foreach ($input_value['options'] as $key => $value) {
			$input_id = md5($key . $input_key);
			$html .= '<input id="' . $input_id . '" value="' . $value . '"' . static::build_input_attr($input_value);
			$html .= ($input_value['checked'] === $value) ? ' checked="checked">' : '>';

			$html .= '<label for="' . $input_id . '" class="radio">' . $key . '</label>';
		}unset($key, $value);
		$html .= '</div>';

		return $html;
	}

	protected function build_checkbox($input_value, $input_key) {
		$html = '<div' . static::build_input_id($input_value) . ' class="field' . static::get_class($input_value, true) . '">';
		$html .= static::build_label($input_value);
		foreach ($input_value['options'] as $key => $value) {
			$input_id = md5($key . $input_key);
			$html .= '<input id="' . $input_id . '" value="' . $value . '"' . static::build_input_attr($input_value);
			if (is_array($input_value['checked'])) {
				$html .= in_array($value, $input_value['checked']) ? ' checked="checked">' : '>';
			} else {
				$html .= ($input_value['checked'] === $value) ? ' checked="checked">' : '>';
			}

			$html .= '<label for="' . $input_id . '" class="checkbox">' . $key . '</label>';
		}unset($key, $value);
		$html .= '</div>';

		return $html;
	}

	protected function build_hidden($input_value, $input_key) {
		$html = '<input' . static::build_input_id($input_value) . static::build_input_attr($input_value) . '>';
		return $html;
	}

	protected function build_input($input_value, $input_key) {
		$has_addons = ($input_value['addon_left'] or $input_value['addon_right']) ? true : false;

		if ($has_addons) {
			$html = static::build_label($input_value);
			$html .= '<div class="field has-addons">';
		} else {
			$html = '<div class="field">';
			$html .= static::build_label($input_value);
		}

		// class
		$class = '';
		$class .= $has_addons ? ' is-expanded' : '';
		$class .= $input_value['icon_left'] ? ' has-icons-left' : '';
		$class .= $input_value['icon_right'] ? ' has-icons-right' : '';

		// addon left
		if ($input_value['addon_left']) {
			$html .= '<div class="control">' . $input_value['addon_left'] . '</div>';
		}

		// input and icon
		$html .= '<div class="control' . $class . '">';
		$html .= '<input' . static::build_input_id($input_value) . static::build_input_attr($input_value) . '>';
		$html .= $input_value['icon_left'] ? '<span class="icon is-left">' . $input_value['icon_left'] . '</span>' : '';
		$html .= $input_value['icon_right'] ? '<span class="icon is-right">' . $input_value['icon_right'] . '</span>' : '';
		$html .= '</div>';

		// addon right
		if ($input_value['addon_right']) {
			$html .= '<div class="control">' . $input_value['addon_right'] . '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	protected function build_image_upload($input_value, $input_key) {
		$id                        = $input_value['id'] . '-' . $input_key;
		$input_value['data']['id'] = $id;

		$html = '<div id="' . $id . '" class="field' . static::get_class($input_value, true) . '">';
		$html .= static::build_label($input_value);
		$html .= '<div class="field"><div class="ajax-message"></div></div>';

		$html .= '<div class="field">';
		$html .= '<a><img class="thumbnail" src="' . $input_value['thumbnail'] . '" height="' . $input_value['thumbnail_size']['height'] . '" width="' . $input_value['thumbnail_size']['width'] . '"></a>';
		$html .= $input_value['delete_button'] ? '<a class="delete" data-id="' . $id . '" data-file_id="' . $input_value['file_id'] . '"></a>' : '';
		$html .= '<div class="file">';
		$html .= '<input accept="image/*"' . static::build_input_attr($input_value) . '>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '
		<script type="text/javascript">
			var fileupload = document.querySelector("#' . $id . ' input[type=\'file\']");
			var image = document.querySelector("#' . $id . ' .thumbnail");
			image.onclick = function () {
			    fileupload.click();
			};
		</script>';

		$html .= '</div>';
		return $html;
	}

	protected function build_file_upload($input_value, $input_key) {
		$id                        = $input_value['id'] . '-' . $input_key;
		$input_value['data']['id'] = $id;

		$html = '<div id="' . $id . '" class="field' . static::get_class($input_value, true) . '">';
		$html .= '<div class="field"><div class="ajax-message"></div></div>';
		$html .= '<div class="columns is-mobile is-vcentered">';

		$html .= '<div class="column">';
		$html .= '<div class="file has-name is-fullwidth">';
		$html .= '<label class="file-label">';
		$html .= '<input' . static::build_input_attr($input_value) . '>';
		$html .= '<span class="file-cta">';
		$html .= '<span class="file-icon"><i class="fa fa-upload"></i></span>';
		$html .= '<span class="file-label">' . $input_value['label'] . '</span>';
		$html .= '</span>';
		$html .= '<span class="file-name">' . $input_value['file_name'] . '</span>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';

		if ($input_value['delete_button']) {
			$html .= '<div class="column is-narrow">';
			$html .= '<a class="delete" data-id="' . $id . '" data-file_id="' . $input_value['file_id'] . '"></a>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	protected function build_html($input_value, $input_key) {
		return $input_value['value'];
	}

	protected function build_textarea($input_value, $input_key) {
		$html = '<div class="field">';
		$html .= static::build_label($input_value);
		$html .= '<textarea' . static::build_input_id($input_value) . static::build_input_attr($input_value) . '>' . $input_value['value'] . '</textarea>';
		$html .= '</div>';
		return $html;
	}

	protected function build_submit_button() {
		if (!$this->submit_text) {
			return;
		}
		$this->html .= '<div class="field is-grouped is-grouped-centered">';
		$this->html .= '<button type="submit" data-text="' . $this->submit_text . '" class="button' . $this->get_submit_class(true) . '"' . ($this->submit_disabled ? ' disabled="disabled"' : '') . '>' . $this->submit_text . '</button>';
		$this->html .= '</div>';
	}

	protected function build_form_footer() {
		$this->html .= '</form>';
	}

	/**
	 *@since 2019.08.29
	 *构造表单属性
	 */
	protected function build_form_attr() {
		$attr = '';
		foreach ($this->form_attr as $key => $value) {
			if (!$value and !is_numeric($value)) {
				continue;
			}

			$attr .= ' ' . $key . '="' . $value . '"';
		}
		unset($key, $value);

		return $attr;
	}

	protected static function build_input_id($input_value) {
		if ($input_value['id'] ?? false) {
			return ' id="' . $input_value['id'] . '"';
		}
		return '';
	}

	/**
	 *@since 2019.07.19
	 *统一封装获取字段attribute
	 *不含：id
	 *不含：下拉selected
	 *不含：单选、复选checked属性
	 *不含：Textarea value属性
	 */
	protected static function build_input_attr($input_value) {
		$bool_attrs   = ['readonly', 'disabled', 'autofocus', 'required', 'multiple'];
		$normal_attrs = ['class', 'value', 'type', 'name', 'placeholder', 'size', 'maxlength', 'min', 'max', 'step', 'pattern'];
		$attr         = '';

		/**
		 *@since 2020.04.17
		 *文件上传及图像上传
		 *
		 *上传字段type值为自定义值，此处需要矫正为合规的 HTML Type：file
		 *适应bulma样式，需要统一添加class：file-input
		 */
		if (in_array($input_value['type'], ['image_upload', 'file_upload'])) {
			$input_value['type']  = 'file';
			$input_value['class'] = 'file-input ' . $input_value['class'];
		}

		foreach ($input_value as $key => $value) {
			// 读取class传参，并根据type设置添加默认class
			if ('class' == $key) {
				if (in_array($input_value['type'], static::$input_types)) {
					$value = 'input ' . $value;
				} else {
					$value = $input_value['type'] . ' ' . $value;
				}

				$attr .= ' ' . $key . '="' . $value . '"';
				continue;
			}

			// Textarea 文本框不设置value属性
			if ('textarea' == $input_value['type'] and 'value' == $key) {
				continue;
			}

			// 移除未设定的属性
			if (!$value and !is_numeric($value)) {
				continue;
			}

			/**
			 *构建Data数据
			 *@since 2020.04.14
			 */
			if ('data' == $key) {
				foreach ($value as $data_key => $data_value) {
					$attr .= ' data-' . $data_key . '="' . $data_value . '" ';
				}unset($data_key, $data_value);
				continue;
			}

			// 设置布尔属性
			if (in_array($key, $bool_attrs)) {
				$attr .= ' ' . $key . '="' . $key . '"';
				continue;
			}

			// 设置常规属性
			if (in_array($key, $normal_attrs)) {
				$attr .= ' ' . $key . '="' . $value . '"';
				continue;
			}
		}
		unset($key, $value);

		return $attr;
	}

	/**
	 *@since 2019.08.25
	 *构建label HTML
	 *
	 *@var string 	$label
	 *@var string 	$required
	 */
	protected static function build_label($input_value) {
		if (empty($input_value['label'])) {
			return '';
		}
		$label = $input_value['required'] ? $input_value['label'] . '<span class="required">*</span>' : $input_value['label'];

		return '<label class="label">' . $label . '</label>';
	}

	/**
	 *辅助函数
	 */
	protected static function get_class($input_value, $space = false) {
		if ($input_value['class'] ?? false) {
			return $space ? ' ' . $input_value['class'] : $input_value['class'];
		}
		return '';
	}

	protected function get_submit_class($space = false) {
		if ($this->submit_class) {
			return $space ? ' ' . $this->submit_class : $this->submit_class;
		}
		return '';
	}

	/**
	 *获取表单字段HTML
	 *@since 2019.04.28
	 */
	public function get_input_fields() {
		return $this->build_input_values();
	}

	// 获取当前表单的组成数据数组（通常用于配合 filter 过滤）
	public function get_input_values() {
		return $this->input_values;
	}
}
