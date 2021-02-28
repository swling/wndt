<?php
namespace Wndt\Model;

use Wndt\Controller\Wndt_API;
use Wndt\Utility\Wndt_Upgrader;
use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *@since 2019.10.27
 *Init
 *
 *注册类型
 *
 *注册分类
 *
 *文章链接
 *
 *用户链接
 *
 *默认Hook
 */
class Wndt_Init {

	use Wnd_Singleton_Trait;

	public $people_label;
	public $supply_label;
	public $demand_label;
	public $enable_people;
	public $enable_company;

	private function __construct() {
		$this->load_file();

		$this->company_label  = wndt_get_config('company_label') ?: '公司';
		$this->people_label   = wndt_get_config('people_label') ?: '名片';
		$this->supply_label   = wndt_get_config('supply_label') ?: '供应';
		$this->demand_label   = wndt_get_config('demand_label') ?: '需求';
		$this->enable_people  = wndt_get_config('enable_people');
		$this->enable_company = wndt_get_config('enable_company');

		$this->init();

		// API
		new Wndt_API;

		// 自定义升级
		if (is_admin()) {
			new Wndt_Upgrader;
		}
	}

	/**
	 *Init
	 */
	private function init() {
		// 注册类型
		add_action('init', [$this, 'register_post_type']);
		add_action('init', [$this, 'register_taxonomy']);

		// 固定连接
		add_filter('post_type_link', [$this, 'filter_post_link'], 11, 2);
		add_action('init', [$this, 'filter_post_rewrite_rules'], 11);

		// 用户链接
		add_filter('author_link', [$this, 'filter_author_link'], 11, 2);
		add_filter('author_rewrite_rules', [$this, 'filter_author_rewrite_rules']);

		/**
		 *@since 2019.10.08
		 *主题安装初始化
		 */
		add_action('after_switch_theme', 'Wndt\Model\Wndt_Admin::install');

		/**
		 * @since 2019.07.22
		 * 开启友情链接
		 */
		add_filter('pre_option_link_manager_enabled', '__return_true');
	}

	/**
	 *加载文件
	 */
	private function load_file() {
		// hook
		require TEMPLATEPATH . '/includes/hook/add-action-wp.php'; //WordPress动作
		require TEMPLATEPATH . '/includes/hook/add-action.php'; //自定义动作
		require TEMPLATEPATH . '/includes/hook/add-filter-wp.php'; //WordPress钩子
		require TEMPLATEPATH . '/includes/hook/add-filter.php'; //自定义钩子

		// functions
		require TEMPLATEPATH . '/includes/function/inc-general.php'; //通用函数定义
		require TEMPLATEPATH . '/includes/function/inc-optimization.php'; //优化
		require TEMPLATEPATH . '/includes/function/inc-comment.php'; //评论

		// temples
		require TEMPLATEPATH . '/includes/function/tpl-general.php'; //通用模板
	}

	################################################################################ 自定义文章类型
	public function register_post_type() {

		//项目
		$labels = [
			'name'              => __('项目', 'wndt'),
			'singular_name'     => __('项目', 'wndt'),
			'add_new'           => __('新建项目', 'wndt'),
			'add_new_item'      => __('新建一个项目', 'wndt'),
			'parent_item_colon' => '',
			'menu_name'         => __('项目', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'description'  => '项目',
			'public'       => true,

			'rewrite'      => ['slug' => 'project', 'with_front' => false],
			'supports'     => ['title', 'author', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
			'show_in_rest' => false,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-playlist-audio',
		];
		register_post_type('project', $args);

		//项目
		$labels = [
			'name'              => __('产品', 'wndt'),
			'singular_name'     => __('产品', 'wndt'),
			'add_new'           => __('新建产品', 'wndt'),
			'add_new_item'      => __('新建一个产品', 'wndt'),
			'parent_item_colon' => '',
			'menu_name'         => __('产品', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'description'  => '产品',
			'public'       => true,

			'rewrite'      => ['slug' => 'product', 'with_front' => false],
			'supports'     => ['title', 'author', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
			'show_in_rest' => false,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-playlist-audio',
		];
		register_post_type('product', $args);
	}

	################################################################################ 定义类型分类
	public function register_taxonomy() {
		// 项目分类
		$labels = [
			'name'          => __('项目分类', 'wndt'),
			'singular_name' => __('项目分类', 'wndt'),
			'menu_name'     => __('项目分类', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => ['slug' => 'project-cat', 'with_front' => false],
		];
		register_taxonomy('project_cat', 'project', $args);

		/**
		 *@since 2018.12.30
		 *定义供应类文章分类
		 */
		$labels = [
			'name'          => _x('产品分类', 'wndt'),
			'singular_name' => _x('产品分类', 'wndt'),
			'menu_name'     => __('产品分类', 'wndt'),
		];
		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => ['slug' => 'product-category', 'with_front' => false],
		];
		register_taxonomy('product_cat', 'product', $args);

		/**
		 * @since 2018.12.30
		 *定义供应类文章标签
		 */
		$labels = [
			'name'          => __('产品标签', 'wndt'),
			'singular_name' => __('产品标签', 'wndt'),
			'menu_name'     => __('产品标签'),
		];
		$args = [
			'labels'       => $labels,
			'hierarchical' => false,
			'rewrite'      => ['slug' => 'product-tag', 'with_front' => false],
		];
		register_taxonomy('product_tag', 'product', $args);
	}

	/**
	 *@since 2019
	 *自定义文章类型链接重写
	 *格式：/$post_type/$post_id
	 */
	public function filter_post_link($link, $post) {
		$post_types = get_post_types(['public' => true, '_builtin' => false], 'names', 'and');
		if (in_array($post->post_type, array_keys($post_types))) {
			return home_url($post_types[$post->post_type] . '/' . $post->ID);
		}

		return $link;
	}

	/**
	 *@since 2019
	 *重写自定义文章类型伪静态
	 **/
	public function filter_post_rewrite_rules() {
		$post_types = get_post_types(['public' => true, '_builtin' => false], 'names', 'and');
		foreach ($post_types as $post_type) {
			add_rewrite_rule(
				$post_type . '/([0-9]+)?$',
				'index.php?post_type=' . $post_type . '&p=$matches[1]',
				'top'
			);

			//comment 翻页
			add_rewrite_rule(
				$post_type . '/([0-9]+)?/comment-page-([0-9]+)?$',
				'index.php?post_type=' . $post_type . '&p=$matches[1]&cpage=$matches[2]',
				'top'
			);
		}
		unset($post_type);
	}

	/**
	 *用户主页链接
	 */
	public function filter_author_link($link, $author_id) {
		$link_base = trailingslashit(get_option('home'));

		// 别名
		// $user_data = get_userdata($author_id);
		// $user_base = 'people';
		// $link = "$user_base/$user_data->user_nicename";

		// ID
		$user_base = 'user';
		$link      = "$user_base/$author_id";

		return $link_base . $link;
	}

	// 用户链接伪静态规则
	public function filter_author_rewrite_rules() {
		$author_rewrite = [];

		// 用户id类
		$author_rewrite["user/([0-9]+)/page/?([0-9]+)/?$"] = 'index.php?author=$matches[1]&paged=$matches[2]';
		$author_rewrite["user/([0-9]+)/?$"]                = 'index.php?author=$matches[1]';
		// 别名类
		$author_rewrite['user/(.*)/page/?([0-9]+)/?$'] = 'index.php?author_name=$matches[1]&paged=$matches[2]';
		$author_rewrite['user/(.*)/?$']                = 'index.php?author_name=$matches[1]';
		return $author_rewrite;
	}
}
