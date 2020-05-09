<?php
namespace Wndt\Model;

use Exception;
use Wnd\Model\Wnd_Sms;

/**
 *@since 2020.04.29
 *短信通知
 *
 */
class Wndt_Sms {

	// 审核通过短信模板代码
	protected static $profile_pass_template = '560066';

	// 审核拒绝短信模板代码
	protected static $profile_refuse_template = '560071';

	/**
	 *profile审核通过通知
	 */
	public static function send_profile_pass_sms($user_id) {
		$phone = wnd_get_user_phone($user_id);
		if (!$phone) {
			return;
		}

		static::send($phone, static::$profile_pass_template);
	}

	/**
	 *profile审核拒绝通知
	 */
	public static function send_profile_refuse_sms($user_id) {
		$phone = wnd_get_user_phone($user_id);
		if (!$phone) {
			return;
		}

		static::send($phone, static::$profile_refuse_template);
	}

	/**
	 *发送短信
	 */
	protected static function send($phone, $template) {
		if (!$phone) {
			return;
		}

		// 发送短信通知
		try {
			$sms = Wnd_Sms::get_instance();
			$sms->set_phone($phone);
			$sms->set_template($template);
			$sms->send();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
}
