<?php
namespace Wnd\Component\Alipay;

/**
 *@since 2019.03.02 支付宝网页支创建类
 */
class AlipayPagePayBuilder {

	protected $gateway_url;
	protected $app_id;
	protected $return_url;
	protected $notify_url;
	protected $charset;

	protected $method;
	protected $product_code;
	protected $private_key;

	protected $total_amount;
	protected $out_trade_no;
	protected $subject;

	public function __construct() {
		$this->charset = 'utf-8';
	}

	// 通过外部配置修改内部受保护的类属性
	public function __set($var, $val) {
		$this->$var = $val;
	}

	/**
	 * 发起订单
	 * @return array
	 */
	public function doPay() {
		//请求参数
		$requestConfigs = [
			'out_trade_no' => $this->out_trade_no,
			'product_code' => $this->product_code,
			'total_amount' => $this->total_amount, //单位 元
			'subject'      => $this->subject, //订单标题
		];
		$commonConfigs = [
			//公共参数
			'app_id'      => $this->app_id,
			'method'      => $this->method, //接口名称
			'format'      => 'JSON',
			'return_url'  => $this->return_url,
			'charset'     => $this->charset,
			'sign_type'   => 'RSA2',
			'timestamp'   => date('Y-m-d H:i:s'),
			'version'     => '1.0',
			'notify_url'  => $this->notify_url,
			'biz_content' => json_encode($requestConfigs),
		];
		$commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);
		return $this->buildRequestForm($commonConfigs);
	}

	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @return 提交表单HTML文本
	 */
	protected function buildRequestForm($para_temp) {

		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->gateway_url . "?charset=" . $this->charset . "' method='POST'>";
		foreach ($para_temp as $key => $val) {
			if (false === $this->checkEmpty($val)) {
				$val = str_replace("'", "&apos;", $val);
				$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'>";
			}
		}unset($key, $val);
		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;'></form>";
		$sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
		return $sHtml;
	}

	public function generateSign($params, $signType = "RSA") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	protected function sign($data, $signType = "RSA") {
		$priKey = $this->private_key;
		$res    = "-----BEGIN RSA PRIVATE KEY-----\n" .
		wordwrap($priKey, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";
		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
		if ("RSA2" == $signType) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
		} else {
			openssl_sign($data, $sign, $res);
		}
		$sign = base64_encode($sign);
		return $sign;
	}

	/**
	 * 校验$value是否非空
	 * if not set ,return true;
	 * if is null , return true;
	 **/
	protected function checkEmpty($value) {
		if (!isset($value)) {
			return true;
		}

		if ($value === null) {
			return true;
		}

		if (trim($value) === "") {
			return true;
		}

		return false;
	}

	public function getSignContent($params) {
		ksort($params);
		$stringToBeSigned = "";
		$i                = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) and "@" != substr($v, 0, 1)) {
				// 转换成目标字符集
				$v = $this->characet($v, $this->charset);
				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset($k, $v);
		return $stringToBeSigned;
	}

	/**
	 * 转换字符集编码
	 * @param $data
	 * @param $targetCharset
	 * @return string
	 */
	protected function characet($data, $targetCharset) {
		if (!empty($data)) {
			$fileType = $this->charset;
			if (strcasecmp($fileType, $targetCharset) != 0) {
				$data = mb_convert_encoding($data, $targetCharset, $fileType);
				//$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
			}
		}
		return $data;
	}
}
