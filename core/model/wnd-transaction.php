<?php
namespace Wnd\Model;

use Exception;

/**
 *@since 2019.09.24
 *定义站内订单、充值、支付公共部分的抽象类
 *
 */
abstract class Wnd_Transaction {

	// order / recharge Post ID
	protected $ID;

	// 站点用户ID
	protected $user_id;

	// 产品ID 对应WordPress产品类型Post ID
	protected $object_id;

	// 金额
	protected $total_amount;

	// 支付标题：产品标题 / 充值标题 / 其他自定义
	protected $subject;

	// 状态
	protected $status;

	/**
	 *@since 2019.08.11
	 *构造函数
	 */
	public function __construct() {
		$this->user_id = get_current_user_id();
	}

	/**
	 *@since 2019.08.12
	 *指定Post ID (order/recharge/payment)
	 **/
	public function set_ID(int $ID) {
		$this->ID = $ID;
	}

	/**
	 *@since 2019.08.12
	 *设定金额
	 **/
	public function set_total_amount($total_amount) {
		if (!is_numeric($total_amount)) {
			throw new Exception(__('金额无效', 'wnd'));
		}

		$this->total_amount = $total_amount;
	}

	/**
	 *@since 2019.08.11
	 *指定产品ID
	 **/
	public function set_object_id(int $object_id) {
		$post = $object_id ? get_post($object_id) : false;
		if ($object_id and !$post) {
			throw new Exception(__('商品ID无效', 'wnd'));
		}

		$this->object_id = $object_id;
	}

	/**
	 *@since 2019.08.11
	 *指定用户，默认为当前用户
	 **/
	public function set_user_id(int $user_id) {
		if (!get_user_by('ID', $user_id)) {
			throw new Exception(__('用户ID无效', 'wnd'));
		}

		$this->user_id = $user_id;
	}

	/**
	 *@since 2019.08.12
	 *设定订单标题
	 **/
	public function set_subject(string $subject) {
		$this->subject = $subject;
	}

	/**
	 *@since 2019.02.11
	 *创建：具体实现在子类中定义
	 */
	abstract public function create();

	/**
	 *@since 2019.02.11
	 *校验：具体实现在子类中定义
	 *通常校验用于需要跳转第三方支付平台的交易
	 */
	abstract public function verify();

	/**
	 *获取WordPress order/recharge post ID
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 *获取支付订单标题
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 *@since 2019.08.12
	 *获取关联产品/服务Post ID
	 **/
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 *@since 2019.08.12
	 *获取消费金额
	 **/
	public function get_total_amount() {
		return $this->total_amount;
	}
}
