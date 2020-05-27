<?php
namespace Wnd\Model;

use Exception;

/**
 *@since 2019.08.11
 *支付模块
 *
 *	# 自定义文章类型
 *	以下 post_type 并未均为私有属性('public' => false)，因此在WordPress后台无法查看到
 *
 *	充值：recharge
 *	消费、订单：order
 *
 *	# 状态：
 *	pending / success
 *
 *	# 充值、消费post data
 *	金额：post_content
 *	关联：post_parent
 *	标题：post_title
 *	状态：post_status: pengding / success
 *	类型：post_type：recharge / order
 *
 */
class Wnd_Payment extends Wnd_Transaction {

	// 基于$this->ID生成，发送至第三方平台的订单号
	protected $out_trade_no;

	// 站点前缀，用于区分订单号
	protected static $site_prefix;

	// 站点名
	protected static $site_name;

	/**
	 *@since 2019.08.11
	 *构造函数
	 */
	public function __construct() {
		parent::__construct();

		/**
		 *构建包含当前站点标识的订单号码作为发送至三方支付平台的订单号
		 *
		 *为防止多站点公用一个支付应用id，或测试环境与正式环境中产生重复的支付订单id，在充值id的前缀前，添加了基于该站点home_url()的前缀字符
		 *@since 2019.03.04
		 *
		 *不采用别名做订单的原因：在WordPress中，不同类型的post type别名可以是重复的值，会在一定程度上导致不确定性，同时根据别名查询post的语句也更复杂
		 *该前缀对唯一性要求不高，仅用于区分上述情况下的冲突
		 *build_site_prefix基于md5，组成为：数字字母，post_id为整数，因而分割字符需要回避数字和字母
		 *@since 2019.03.04
		 *
		 */
		static::$site_prefix = strtoupper(substr(md5(home_url()), 0, 4));

		/**
		 *站点名称
		 *跳转至第三方平台时，在站内订单或充值标题前加上站点名以便用户识别
		 *@since 2019.12.21
		 *
		 */
		static::$site_name = get_bloginfo('name');
	}

	/**
	 *设置支付平台的支付订单号
	 *@since 2019.08.11
	 *@param string 	$out_trade_no 	支付平台订单号
	 */
	public function set_out_trade_no($out_trade_no) {
		$this->out_trade_no = $out_trade_no;
	}

	/**
	 *从第三方支付平台订单号，解析出本站对应的Post ID
	 *@param 	string 	$out_trade_no 	支付平台订单号
	 *@return 	int|0 	order|recharge Post ID
	 */
	protected function parse_out_trade_no($out_trade_no) {
		if (false === strpos($out_trade_no, static::$site_prefix . '-')) {
			return 0;
		}

		list($prefix, $ID) = explode('-', $out_trade_no, 2);
		if ($prefix != static::$site_prefix) {
			return 0;
		}
		return (int) $ID;
	}

	/**
	 *@since 2019.02.17 创建在线支付信息 订单 / 充值
	 *
	 *若设置了object_id 调用：insert_order 否则调用: insert_recharge
	 *
	 *@param int 		$this->user_id  	required
	 *@param float  	$this->total_money	required when !$object_id
	 *@param int 		$this->object_id  	option
	 *@param string 	$this->subject 		option
	 */
	public function create() {
		if (!$this->user_id) {
			throw new Exception(__('请登录', 'wnd'));
		}

		// 在线订单 / 充值
		if ($this->object_id) {
			$order = new Wnd_Order();
			$order->set_object_id($this->object_id);
			$order->create();

			$this->ID           = $order->get_ID();
			$this->subject      = $order->get_subject();
			$this->total_amount = $order->get_total_amount();

		} else {
			$recharge = new Wnd_Recharge();
			$recharge->set_total_amount($this->total_amount);
			$recharge->create();

			$this->ID           = $recharge->get_ID();
			$this->subject      = $recharge->get_subject();
			$this->total_amount = $recharge->get_total_amount();
		}
	}

	/**
	 *@since 2019.02.11
	 *充值付款校验
	 *@return int|Exception 	order ID|recharge ID if success
	 *
	 *@param int 				$this->ID  				required if !$this->out_trade_no
	 *@param string 			$this->out_trade_no	  	required if !$this->ID
	 *@param float  			$this->total_money		required
	 */
	public function verify() {
		$type     = !empty($_POST) ? __('异步', 'wnd') : __('同步', 'wnd');
		$this->ID = $this->ID ?: $this->parse_out_trade_no($this->out_trade_no);

		// 校验
		$post = get_post($this->ID);
		if (!$this->ID or !$post) {
			throw new Exception(__('支付ID无效：', 'wnd') . $this->ID);
		}
		if ($post->post_content != $this->total_amount) {
			throw new Exception(__('金额不匹配', 'wnd'));
		}

		// 定义变量
		$this->ID        = $post->ID;
		$this->subject   = $post->post_title . '(' . $type . ')';
		$this->object_id = $post->post_parent;

		// 订单支付状态检查
		if ('success' == $post->post_status) {
			return $this->ID;
		}
		if ($post->post_status != 'pending') {
			throw new Exception(__('订单状态无效', 'wnd'));
		}

		// 更新 订单/充值
		if ($post->post_parent) {
			$order = new Wnd_Order();
			$order->set_ID($this->ID);
			$order->set_subject($this->subject);
			$order->verify();

		} else {
			$recharge = new Wnd_Recharge();
			$recharge->set_ID($this->ID);
			$recharge->set_subject($this->subject);
			$recharge->verify();
		}

		/**
		 * @since 2019.06.30
		 *成功完成付款后
		 */
		do_action('wnd_payment_verified', $this->ID);
		return $this->ID;
	}

	/**
	 *构建包含当前站点标识的订单号码作为发送至三方支付平台的订单号
	 */
	public function get_out_trade_no() {
		if (!$this->ID) {
			throw new Exception(__('站内支付数据尚未写入，无法生成订单号', 'wnd'));
		}

		return static::$site_prefix . '-' . $this->ID;
	}

	/**
	 *@since 2019.12.21
	 *在站内标题基础上加上站点名称，便于用户在第三方支付平台识别
	 *
	 */
	public function get_subject() {
		return static::$site_name . ' - ' . $this->subject;
	}

	/**
	 *@since 2020.04.12
	 *支付成功后返回链接
	 *@param int $object_id 支付产品ID；为空则为充值
	 */
	public static function get_return_url($object_id = 0) {
		// 订单
		if ($object_id) {
			$url = get_permalink($object_id) ?: (wnd_get_config('pay_return_url') ?: home_url());

			// 充值
		} else {
			$url = wnd_get_config('pay_return_url') ?: home_url();
		}

		return $url;
	}
}
