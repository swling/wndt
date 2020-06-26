<?php

namespace Wndt\Component\AjaxComment;

/***
 *Ajax添加评论(Rest API)
 *@since 2019.12.17
 *
 */
class AjaxComment {

	// 版本号
	protected static $ver = 0.12;

	//your comment wrapprer
	protected $wrapper = 'comment-list';

	//默认为bottom，如果你的表单在顶部则设置为top
	protected $formpostion = 'bottom';

	public function __construct() {
		add_action('rest_api_init', [$this, 'register_rest_route']);
		add_action('wp_enqueue_scripts', [$this, 'ajax_comment_scripts']);
	}

	/**
	 *JavaScript
	 *
	 */
	public function ajax_comment_scripts() {
		if (is_singular()) {
			/**
			 *引入js文件
			 */
			wp_enqueue_script(
				'ajax-comment', WNDT_URL . '/includes/component/AjaxComment/main.min.js',
				['jquery'],
				static::$ver
			);

			/**
			 *定义js变量
			 */
			wp_localize_script(
				'ajax-comment',
				'ajaxcomment',
				[
					'api_url'     => site_url() . '/wp-json/wndt/comment',
					'rest_nonce'  => wp_create_nonce('wp_rest'),
					'order'       => get_option('comment_order'),
					'wrapper'     => $this->wrapper,
					'formpostion' => $this->formpostion,
				]
			);
		}
	}

	/**
	 *注册路由
	 */
	public function register_rest_route() {
		register_rest_route(
			'wndt',
			'comment',
			[
				'methods'  => ['POST', 'GET'],
				'callback' => __CLASS__ . '::add_comment',
			]
		);
	}

	/**
	 *写入评论
	 */
	public static function add_comment(): array{
		$comment = wp_handle_comment_submission(wp_unslash($_POST));
		$user    = wp_get_current_user();
		if (is_wp_error($comment)) {
			return ['status' => 0, 'msg' => $comment->get_error_message()];
		}

		do_action('set_comment_cookies', $comment, $user);
		$GLOBALS['comment'] = $comment;

		/**
		 *敬请留意：
		 *此结构可能随着WordPress wp_list_comments()输出结构变化而失效
		 */
		$html = '<li class="' . implode(' ', get_comment_class()) . '">';
		$html .= '<article class="comment-body">';
		$html .= '<footer class="comment-meta">';
		$html .= '<div class="comment-author vcard">';
		$html .= get_avatar($comment, $size = '56');
		$html .= '<b class="fn">' . get_comment_author_link() . '</b>';
		$html .= '</div>';
		$html .= '<div class="comment-metadata">' . get_comment_date('', $comment) . ' ' . get_comment_time() . '</div>';
		$html .= '</footer>';
		$html .= '<div class="comment-content">' . get_comment_text() . '</div>';
		$html .= '</article>';
		$html .= '</li>';

		return ['status' => 1, 'msg' => '提交成功', 'data' => $html];
	}
}
