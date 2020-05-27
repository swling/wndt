<?php
namespace Wnd\Component\Alipay;

/**
 * 支付宝配置信息
 */
class AlipayConfig {

	public static function getConfig() {
		/**
		 *@since 2019.03.02 请根据注释说明，修改支付宝配置信息，
		 *示例代码中采用的是函数调用，为WndWP插件专用务必修改后才能用于其他网站
		 */
		$config = [
			//应用ID,您的APPID。
			'app_id'               => wnd_get_config('alipay_appid'),

			/**
			 *@link https://docs.open.alipay.com/58/103242
			 *商户私钥 用工具生成的应用私钥
			 **/
			'merchant_private_key' => wnd_get_config('alipay_private_key'),

			/**
			 *支付宝公钥 @link https://openhome.alipay.com/platform/keyManage.htm
			 *对应APPID下的支付宝公钥。用工具生成应用公钥后，上传到支付宝，再由支付宝生成
			 */
			'alipay_public_key'    => wnd_get_config('alipay_public_key'),

			//异步通知地址 *不能带参数否则校验不过 （插件执行页面地址）
			'notify_url'           => wnd_get_do_url(),

			//同步跳转 *不能带参数否则校验不过 （插件执行页面地址）
			'return_url'           => wnd_get_do_url(),

			//编码格式
			'charset'              => "UTF-8",

			//签名方式
			'sign_type'            => "RSA2",

			//支付宝网关
			'gateway_url'          => "https://openapi.alipay.com/gateway.do",
		];

		return $config;
	}
}
