<?php
namespace Wnd\Model;

use Exception;

/**
 *根据表单name提取数据
 *@since 2019.03.04
 *
 *@param $verify_form_nonce 	bool 	是否校验表单字段经由Wnd_Form_WP表单类生成
 *@param $_POST 				array 	表单数据
 *
 *
 * 前端表单遵循以下规则定义的name，后台获取后自动提取，并更新到数据库
 *	文章：_post_{$field}
 *
 * 	文章字段：
 *	_meta_{$key} (*自定义数组字段)
 *	_wpmeta_{$key} (*WordPress原生字段)
 *
 * 	Term:
 *	_term_{$taxonomy}(*taxonomy)
 *
 *	用户：_user_{$field}
 *	用户字段：
 *	_usermeta_{$key} (*自定义数组字段)
 *	_wpusermeta_{$key} (*WordPress原生字段)
 *
 */
class Wnd_Form_Data {

	public $form_data;

	public static $form_nonce_name = 'wnd_form_nonce';

	public function __construct($verify_form_nonce = true) {
		if (empty($_POST)) {
			throw new Exception(__('表单数据为空', 'wnd'));
		}

		/**
		 *@since 2019.05.10
		 *apply_filters('wnd_form_data', $_POST) 操作可能会直接修改$_POST
		 *因而校验表单操作应该在filter应用之前执行
		 *通过filter添加的数据，自动视为被允许提交的数据
		 */
		if ($verify_form_nonce and !static::verify_form_nonce()) {
			throw new Exception(__('表单已被篡改', 'wnd'));
		}

		// 允许修改表单提交数据
		$this->form_data = apply_filters('wnd_form_data', $_POST);

		/**
		 *根据表单数据控制表单提交
		 *@since 2019.12.22
		 *
		 */
		$can_array = apply_filters('wnd_can_submit_form', ['status' => 1], $this->form_data);
		if (0 === $can_array['status']) {
			throw new Exception($can_array['msg']);
		}
	}

	/**
	 *
	 *根据前缀提取指定表单数据
	 *
	 *@since 2020.01.04
	 */
	protected function get_data_by_prefix($prefix): array{
		$data = [];
		foreach ($this->form_data as $key => $value) {
			if (0 === strpos($key, $prefix)) {
				$key        = str_replace($prefix, '', $key);
				$data[$key] = $value;
			}
		}unset($key, $value);

		return $data;
	}

	// 获取WordPress user数据数组
	public function get_user_data(): array{
		return $this->get_data_by_prefix('_user_');
	}

	// 获取WordPress原生use meta数据数组
	public function get_wp_user_meta_data(): array{
		return $this->get_data_by_prefix('_wpusermeta_');
	}

	// 获取自定义WndWP user meta数据数组
	public function get_user_meta_data(): array{
		return $this->get_data_by_prefix('_usermeta_');
	}

	// 获取WordPress原生post meta数据数组
	public function get_post_data(): array{
		return $this->get_data_by_prefix('_post_');
	}

	// 获取WordPress原生post meta数据数组
	public function get_wp_post_meta_data(): array{
		return $this->get_data_by_prefix('_wpmeta_');
	}

	// 获取WndWP post meta数据数组
	public function get_post_meta_data(): array{
		return $this->get_data_by_prefix('_meta_');
	}

	// 获取WordPress分类：term数组
	public function get_terms_data(): array{
		return $this->get_data_by_prefix('_term_');
	}

	/**
	 *@since 2019.07.17
	 *获取表单数据
	 *返回表单提交数据
	 *与原$_POST相比，此时获取的表单提交数据，执行了wnd_form_handler filter，并通过了表单一致性校验
	 */
	public function get_form_data(): array{
		return $this->form_data;
	}

	/**
	 *构造nonce表单字段
	 *@since 2020.05.07
	 *@param array 	$form_names 表单所有字段name数组
	 */
	public static function build_form_nonce_field($form_names): string{
		$nonce = static::create_form_nonce($form_names);
		return '<input type="hidden" name="' . static::$form_nonce_name . '" value="' . $nonce . '">';
	}

	/**
	 *构建表单字段
	 *
	 *@since 2019.10.27
	 *
	 *@param array 	$form_names 表单所有字段name数组
	 */
	protected static function create_form_nonce(array $form_names): string{
		// nonce自身字段也需要包含在内
		$form_names[] = static::$form_nonce_name;

		// 去重排序后生成nonce
		$form_names = array_unique($form_names);
		sort($form_names);
		return wp_create_nonce(md5(implode('', $form_names)));
	}

	/**
	 *@since 2019.05.09 校验表单字段是否被篡改
	 *
	 *@see static::create_form_nonce
	 */
	protected static function verify_form_nonce(): bool {
		if (!isset($_POST[static::$form_nonce_name])) {
			return false;
		}

		// 提取POST $_FILES数组键值，去重并排序
		$form_names = array_merge(array_keys($_POST), array_keys($_FILES));
		$form_names = array_unique($form_names);
		sort($form_names);

		// 校验数组键值是否一直
		return wp_verify_nonce($_POST[static::$form_nonce_name], md5(implode('', $form_names)));
	}
}
