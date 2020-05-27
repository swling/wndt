<?php
namespace Wnd\Template;

use Exception;
use Wnd\Action\Wnd_Create_Order;
use Wnd\View\Wnd_Form_WP;

/**
 *@since 2020.03.21
 *
 *付费按钮
 */
class Wnd_Pay_Button {

	/**
	 *定义付费相关公共变量
	 */
	protected static function get_payment_var($post_id): array{
		$post_id = $post_id;
		$post    = $post_id ? get_post($post_id) : false;
		if (!$post) {
			throw new Exception(__('Post ID无效', 'wnd'));
		}

		$user_id       = get_current_user_id();
		$post_price    = wnd_get_post_price($post_id);
		$user_money    = wnd_get_user_money($user_id);
		$user_has_paid = wnd_user_has_paid($user_id, $post_id);
		$primary_color = 'is-' . wnd_get_config('primary_color');
		$second_color  = 'is-' . wnd_get_config('second_color');

		return compact('post', 'user_id', 'post_price', 'user_money', 'user_has_paid', 'primary_color', 'second_color');
	}

	/**
	 * 付费下载
	 *@since 2018.09.17
	 *
	 *价格：wp post meta 	-> price
	 *文件：wnd post meta 	-> file
	 *
	 */
	public static function build_paid_download_button($post_id) {
		extract(static::get_payment_var($post_id));

		$file = wnd_get_post_meta($post_id, 'file');
		// 没有文件
		if (!$file) {
			return;
		}

		$button = '';

		// 未登录用户
		if (!$user_id) {
			$button .= static::build_message('付费下载：¥ ' . $post_price, $second_color);
			$button .= '<div class="field is-grouped is-grouped-centered">';
			$button .= wnd_modal_button(__('登录', 'wnd'), 'wnd_user_center', 'do=login');
			$button .= '</div>';
			return $button;
		}

		if ($user_has_paid) {
			$button_text = '您已购买点击下载';

		} elseif ($user_id == $post->post_author) {
			$button_text = '下载文件';

		} elseif ($post_price > 0) {
			$button_text = '付费下载 ¥ ' . $post_price;

		} else {
			$button_text = '免费下载';
		}

		// 非作者，判断余额支付情况
		if ($user_id == $post->post_author) {
			$button .= static::build_message('您的付费下载：¥ ' . $post_price, $second_color);

		} elseif (!$user_has_paid) {
			try {
				// 创建订单权限检测
				Wnd_Create_Order::check_create($post_id, $user_id);

				// 消费提示
				$button .= static::build_reminder($user_money, $post_price, $second_color);
			} catch (Exception $e) {
				$button .= static::build_message($e->getMessage(), $second_color);
			}
		}

		// 无论是否已支付，均需要提交下载请求，是否扣费将在Wnd\Action\Wnd_Pay_For_Downloads判断
		$form = new Wnd_Form_WP();
		$form->add_hidden('post_id', $post_id);
		$form->set_action('wnd_pay_for_downloads');
		$form->set_submit_button($button_text);
		$form->build();

		$button .= $form->html;
		return $button;
	}

	/**
	 *付费阅读
	 */
	public static function build_paid_reading_button($post_id) {
		extract(static::get_payment_var($post_id));

		// 免费文章
		if (!$post_price) {
			return;
		}

		$button = '';

		// 未登录用户
		if (!$user_id) {
			$button .= static::build_message('付费内容：¥ ' . $post_price, $second_color);
			$button .= '<div class="field is-grouped is-grouped-centered">';
			$button .= wnd_modal_button(__('登录', 'wnd'), 'wnd_user_center', 'do=login');
			$button .= '</div>';
			return $button;
		}

		// 已支付
		if ($user_has_paid) {
			$button .= static::build_message('您已付费：¥ ' . $post_price, $second_color);
			return $button;
		}

		// 作者本人
		if ($user_id == $post->post_author) {
			$button .= static::build_message('您的付费文章：¥ ' . $post_price, $second_color);
			return $button;
		}

		// 已登录未支付
		try {
			// 创建订单权限检测
			Wnd_Create_Order::check_create($post_id, $user_id);

			$form = new Wnd_Form_WP();
			$form->add_hidden('post_id', $post_id);
			$form->set_action('wnd_pay_for_reading');
			$form->set_submit_button('付费阅读： ¥ ' . wnd_get_post_price($post_id));
			$form->build();

			// 消费提示及提交按钮
			$button .= static::build_reminder($user_money, $post_price, $second_color);
			$button .= $form->html;
		} catch (Exception $e) {
			$button .= static::build_message($e->getMessage(), $second_color);
		}

		return $button;
	}

	/**
	 *构建消息
	 *
	 */
	protected static function build_message($message, $color) {
		return wnd_message($message, $color, true);
	}

	/**
	 *构建信息提醒
	 *
	 */
	protected static function build_reminder($user_money, $post_price, $color) {
		$message = __('当前余额：¥ ', 'wnd') . '<b>' . $user_money . '</b>&nbsp;&nbsp;' . __('本次消费：¥ ', 'wnd') . '<b>' . $post_price . '</b>';
		return static::build_message($message, $color);
	}
}
