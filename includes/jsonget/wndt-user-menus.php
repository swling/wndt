<?php
namespace Wndt\Jsonget;

use Wnd\Jsonget\Wnd_Menus;

/**
 *@since 2021.02.27
 *
 *管理员用户中心
 *
 */
class Wndt_User_Menus extends Wnd_Menus {

	// 导航Tabs
	protected static function query($args = []): array{
		if (wnd_is_manager()) {
			$user_menus = static::build_manager_menus();
		} elseif (is_user_logged_in()) {
			$user_menus = static::build_user_menus();
		} else {
			$user_menus = [];
		}

		if ($args['in_side']) {
			return [static::post_type_menus(), $user_menus];
		} else {
			if (wnd_is_manager()) {
				return [$user_menus, static::wndt_add_menus()];
			} else {
				return [$user_menus];
			}
		}
	}

	/**
	 *@since 2019.10.11
	 *自定义类型顶部导航
	 */
	protected static function post_type_menus(): array{
		// 获取所有公开的，有存档的自定义类型
		$all_post_types = get_post_types(
			[
				'public'      => true,
				'show_ui'     => true,
				// '_builtin'    => false,
				'has_archive' => true,
			],
			'names',
			'and'
		);

		$items = [];
		foreach ($all_post_types as $post_type) {
			$items[] = ['title' => get_post_type_object($post_type)->label, 'href' => get_post_type_archive_link($post_type)];
		}
		unset($post_type);

		$menus = [
			'label'  => '站点导航',
			'expand' => false,
			'items'  => $items,
		];

		return $menus;
	}

	/**
	 *@since 2019.10.11
	 *自定义类型顶部导航
	 */
	protected static function wndt_add_menus(): array{
		$menus = [
			'label'  => '主题菜单',
			'expand' => false,
			'items'  => [
				['title' => '赞赏统计', 'href' => static::get_front_page_url() . '#wndt_reward_list'],
			],
		];

		return $menus;
	}
}
