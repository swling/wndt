<?php
namespace Wndt\Module;

use Wnd\Module\Wnd_Module;

/**
 *@since 2020.03.21
 *
 *
 *站内通知
 *
 *根据当前用户信息，引导用户完成推荐的操作建议
 *
 */
class Wndt_Notification extends Wnd_Module {

	protected static function build($args = []): string{
		$user_id      = get_current_user_id();
		$icon         = '<span class="icon"><i class="fa fa-exclamation-triangle"></i></span>';
		$notification = '';

		return $notification ? wnd_notification($icon . $notification, 'is-warning has-text-centered is-marginless', true) : '';
	}
}
