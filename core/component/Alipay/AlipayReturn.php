<?php
namespace Wnd\Component\Alipay;

use Exception;
use Wnd\Component\Alipay\AlipayConfig;
use Wnd\Component\Alipay\AlipayService;
use Wnd\Model\Wnd_Payment;

/**
 *@since 2019.02.11
 *
 *支付宝同步跳转通知
 *
 *@link https://docs.open.alipay.com/204/105301/
 */
class AlipayReturn {

	public static function verify() {
		/**
		 *@since 2019.03.02 支付宝支付同步跳转
		 *同步回调一般不处理业务逻辑，显示一个付款成功的页面，或者跳转到用户的财务记录页面即可。
		 */
		// header('Content-type:text/html; Charset=utf-8');
		$config = AlipayConfig::getConfig();
		$aliPay = new AlipayService($config['alipay_public_key']);
		$result = $aliPay->rsaCheck($_GET);
		if ($result !== true) {
			exit('校验失败');
		}

		/**
		 *验签通过，更新站内订单
		 **/
		try {
			$payment = new Wnd_Payment();
			$payment->set_total_amount($_GET['total_amount']);
			$payment->set_out_trade_no($_GET['out_trade_no']);
			$payment->verify();

			$object_id = $payment->get_object_id();
		} catch (Exception $e) {
			exit($e->getMessage());
		}

		// 跳转链接
		$url = Wnd_Payment::get_return_url($object_id);
		header('Location:' . add_query_arg('from', 'payment_successful', $url));
		exit;
	}
}
