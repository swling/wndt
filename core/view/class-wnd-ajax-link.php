<?php
namespace Wnd\View;

/**
 *ajax请求链接构造类
 *@since 2019.09.28
 */
class Wnd_Ajax_Link {

	protected $text;
	protected $action;
	protected $cancel_action;
	protected $param;
	protected $class;
	protected $html;

	public function set_text($text) {
		$this->text = $text;
	}

	public function set_action($action) {
		$this->action = $action;
	}

	public function set_cancel_action($cancel_action) {
		$this->cancel_action = $cancel_action;
	}

	public function set_param($param) {
		$this->param = (is_array($param) or is_object($param)) ? http_build_query($param) : $param;
	}

	public function set_class($class) {
		$this->class = $class;
	}

	public function get_html() {
		if (!$this->html) {
			$this->build();
		}

		return $this->html;
	}

	/**
	 *@since 2019.07.02
	 *封装一个链接，发送ajax请求到后端
	 *功能实现依赖对应的前端支持
	 **/
	protected function build() {
		$this->html = '<a class="ajax-link ' . $this->class . '" data-is-cancel="0" data-disabled="0"';
		$this->html .= ' data-action="' . $this->action . '"';
		$this->html .= ' data-cancel="' . $this->cancel_action . '" data-param="' . $this->param . '"';
		$this->html .= ' data-action-nonce="' . wp_create_nonce($this->action) . '"';
		$this->html .= ' data-cancel-nonce="' . wp_create_nonce($this->cancel_action) . '"';
		$this->html .= '>';
		$this->html .= $this->text . '</a>';

		return $this->html;
	}
}
