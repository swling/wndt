<?php
namespace Wndt\Model;

use Exception;

/**
 *@since 2019.09.22
 *站内增值服务：Value added service
 *抽象类
 */
abstract class Wndt_VAS {

	protected $user_id;
	protected $user_money;
	protected $number;
	protected $price;
	protected $total_amount;

	public function __construct() {
		$this->user_id = get_current_user_id();
		if (!$this->user_id) {
			throw new Exception('请登录');
		}
		$this->user_money = wnd_get_user_balance($this->user_id);
	}

	/**
	 *指定订阅增值服务数量
	 */
	public function set_number(int $number) {
		$this->number       = $number;
		$this->total_amount = $this->number * $this->price;
	}

	/**
	 *订阅增值服务
	 *具体实现方法需要在子类中定义
	 */
	abstract public function purchase();

	/**
	 *基础通用校验
	 */
	public function check() {
		if (!$this->number) {
			throw new Exception('未指定数量');
		}

		if (!$this->price) {
			throw new Exception('价格信息错误');
		}

		if (!$this->total_amount or $this->total_amount < 0) {
			throw new Exception('消费金额错误：¥' . $this->total_amount);
		}

		if ($this->user_money < $this->total_amount) {
			throw new Exception('余额不足：¥' . $this->user_money);
		}
	}

	/**
	 *获取消费金额
	 */
	public function get_total_amount() {
		return number_format($this->total_amount, 2, '.', '');
	}
}
