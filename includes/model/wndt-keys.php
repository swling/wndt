<?php

namespace Wndt\Model;

use Wnd\Model\Wnd_Order_Props;
use Wnd\Model\Wnd_Transaction;

/**
 * 秘钥储存 wp post meta : secret_keys
 * 需要修改 $post_type 为自定义秘钥商品类型，否则会影响其他 post 商品销售
 */
class Wndt_Keys {

	// 限定 post type
	private static $post_type = 'card';

	public static function hook() {
		// 注册 card 类型
		static::register_post_type();

		// 更新卡券及订单数据，发送邮件
		add_action('wnd_order_completed', function ($order_id, $order) {
			if (static::$post_type != get_post_type($order->object_id)) {
				return;
			}

			static::update_order_props($order);
		}, 10, 2);

		// 库存检测
		add_filter('wnd_can_do_payment', function (array $can_array, int $post_id, $type, $sku_id, $quantity): array {
			if (static::$post_type != get_post_type($post_id)) {
				return $can_array;
			}

			$count = static::get_keys_count($post_id);
			if ($quantity > static::get_keys_count($post_id)) {
				return ['status' => 0, 'msg' => '库存不足！当前剩余库存：' . $count];
			}

			return $can_array;
		}, 11, 5);
	}

	public static function update_order_props(object $order) {
		// 根据购买数量，将已销售的 keys 从库存扣除
		$quantity  = Wnd_Order_Props::get_order_quantity($order->ID) ?: 1;
		$keys      = get_post_meta($order->object_id, 'secret_keys', true);
		$sold_keys = array_splice($keys, -$quantity);
		update_post_meta($order->object_id, 'secret_keys', $keys);

		// 将本次售出的 keys 写入订单 props 备查
		Wnd_Order_Props::update_order_props($order->ID, ['keys' => $sold_keys]);

		// 发送邮件
		$props = json_decode($order->props);
		$email = $props->request->email ?? '';
		if ($email) {
			// 邮件内容
			$subject = '您在 [' . get_option('blogname') . '] 购买的卡券信息';
			$message = implode('<br/>', $sold_keys);

			// 发送邮件
			$headers = 'Content-Type: text/html; charset=' . get_option('blog_charset') . "\n";
			wp_mail($email, $subject, $message, $headers);
		}
	}

	public static function list_key_orders(object $post) {
		// 查询订单并列出
		$handler = Wnd_Transaction::get_instance('order');
		$results = $handler->get_user_valid_orders(get_current_user_id(), $post->ID, 0);

		foreach ($results as $order) {
			echo '订单日期：' . wnd_date('Y-m-d h:i:s', $order->time) . '<br/>';
			$props = json_decode($order->props);
			foreach ($props->keys as $key) {
				echo "秘钥：{$key}<br/>";
			}
		}
	}

	public static function get_keys_count(int $post_id): int {
		$keys = get_post_meta($post_id, 'secret_keys', true);
		return $keys ? count($keys) : 0;
	}

	private static function register_post_type() {
		//项目
		$labels = [
			'name'              => __('卡券', 'wndt'),
			'singular_name'     => __('卡券', 'wndt'),
			'add_new'           => __('新建卡券', 'wndt'),
			'add_new_item'      => __('新建一个卡券', 'wndt'),
			'parent_item_colon' => '',
			'menu_name'         => __('卡券', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'description'  => '卡券',
			'public'       => true,

			'rewrite'      => ['slug' => static::$post_type, 'with_front' => false],
			'supports'     => ['title', 'author', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
			'show_in_rest' => false,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-playlist-audio',
		];
		register_post_type(static::$post_type, $args);

		/**
		 *@since 2018.12.30
		 *定义供应类文章分类
		 */
		$labels = [
			'name'          => _x('卡券分类', 'wndt'),
			'singular_name' => _x('卡券分类', 'wndt'),
			'menu_name'     => __('卡券分类', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => ['slug' => 'card-category', 'with_front' => false],
		];
		register_taxonomy('card_cat', 'card', $args);

		/**
		 * @since 2018.12.30
		 *定义供应类文章标签
		 */
		$labels = [
			'name'          => __('卡券标签', 'wndt'),
			'singular_name' => __('卡券标签', 'wndt'),
			'menu_name'     => __('卡券标签'),
		];
		$args = [
			'labels'       => $labels,
			'hierarchical' => false,
			'rewrite'      => ['slug' => 'card-tag', 'with_front' => false],
		];
		register_taxonomy('card_tag', 'card', $args);
	}
}
