<?php
namespace Wnd\Component\Alipay;

use Exception;
use Wnd\Component\Alipay\AlipayConfig;
use Wnd\Component\Alipay\AlipayPagePayBuilder;
use Wnd\Model\Wnd_Payment;

/**
 *网页支付
 *@since 2019.03.02 轻量化改造，新增wap支付
 */
class AlipayPagePay {

	public static function pay() {
		/**
		 *@since 2019.08.12 面向对象重构
		 *
		 *创建站内支付信息
		 */
		$post_id      = $_REQUEST['post_id'] ?? 0;
		$total_amount = $_REQUEST['total_amount'] ?? 0;
		try {
			$payment = new Wnd_Payment();
			$payment->set_object_id($post_id);
			$payment->set_total_amount($total_amount);
			$payment->create();
		} catch (Exception $e) {
			exit($e->getMessage());
		}

		/**
		 *@since 2019.03.03
		 * 配置支付宝API
		 *
		 * PC支付和wap支付中：product_code 、method 参数有所不同，详情查阅如下
		 *@link https://docs.open.alipay.com/270/alipay.trade.page.pay
		 *@link https://docs.open.alipay.com/203/107090/
		 */
		$config               = AlipayConfig::getConfig();
		$aliPay               = new AlipayPagePayBuilder();
		$aliPay->total_amount = $payment->get_total_amount();
		$aliPay->out_trade_no = $payment->get_out_trade_no();
		$aliPay->subject      = $payment->get_subject();
		$aliPay->product_code = wp_is_mobile() ? 'QUICK_WAP_WAY' : 'FAST_INSTANT_TRADE_PAY';
		$aliPay->method       = wp_is_mobile() ? 'alipay.trade.wap.pay' : 'alipay.trade.page.pay';
		$aliPay->gateway_url  = $config['gateway_url'];
		$aliPay->app_id       = $config['app_id'];
		$aliPay->return_url   = $config['return_url'];
		$aliPay->notify_url   = $config['notify_url'];
		$aliPay->private_key  = $config['merchant_private_key'];

		// 生成数据表单并提交
		echo $aliPay->doPay();
	}
}
