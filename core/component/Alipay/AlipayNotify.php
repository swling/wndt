<?php
namespace Wnd\Component\Alipay;

use Exception;
use Wnd\Component\Alipay\AlipayConfig;
use Wnd\Component\Alipay\AlipayService;
use Wnd\Model\Wnd_Payment;

/**
 *支付宝异步通知
 *
 *@link https://docs.open.alipay.com/204/105301/
 */
class AlipayNotify {

	public static function verify() {
		$config = AlipayConfig::getConfig();

		//支付宝公钥，账户中心->密钥管理->开放平台密钥，找到添加了支付功能的应用，根据你的加密类型，查看支付宝公钥
		$aliPay = new AlipayService($config['alipay_public_key']);
		$result = $aliPay->rsaCheck($_POST);
		if ($result !== true) {
			exit('error');
		}

		/**
		 *请在这里加上商户的业务逻辑程序代
		 *获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		 *
		 *如果校验成功必须输出 'success'，页面源码不得包含其他及HTML字符
		 */
		if ('TRADE_FINISHED' == $_POST['trade_status']) {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
			//如果有做过处理，不执行商户的业务程序

			//注意：
			//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

			exit('success'); //由于是即时到账不可退款服务，因此直接返回成功
		}

		if ('TRADE_SUCCESS' == $_POST['trade_status']) {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
			//如果有做过处理，不执行商户的业务程序
			//注意：
			//付款完成后，支付宝系统发送该交易状态通知
			// app_id
			// $app_id = $_POST['app_id'];

			/**
			 *@since 2019.08.12 异步校验
			 */
			try {
				$payment = new Wnd_Payment();
				$payment->set_total_amount($_POST['total_amount']);
				$payment->set_out_trade_no($_POST['out_trade_no']);
				$payment->verify();
			} catch (Exception $e) {
				exit($e->getMessage());
			}

			// 校验通过
			exit('success');
		}
	}
}
