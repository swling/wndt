<?php
namespace Wndt\Model;

use Exception;
use WP_Comment_Query;

/**
 *@since 2019.12.15
 *
 *点评模型
 *
 *用户点评数据以comment形式记录：点评内容->comment_content / 点评分数：对应comment的comment meta：rating
 *
 *被点评对象数据以对应的post meta记录：点评平均分：rating（wp post meta） /总点评次数：rating_count（wnd post meta）
 *
 *未完成测试（12.15）
 */
class Wndt_Rating {

	protected $post_id;
	protected $user_id;
	protected $rating;
	protected $content;

	// 指定post累积点评次数 wnd post meta
	protected $rating_count;

	// 指定post评价点评分数 post meta
	protected $rating_avg;

	// 当前用户历史点评comment
	protected $user_old_review;

	// 当前用户历史点评分数comment meta
	protected $user_old_rating;

	public function __construct() {
		$this->user_id = get_current_user_id();
		if (!$this->user_id) {
			throw new Exception('请登录');
		}
	}

	public function set_rating($rating) {
		$this->rating = $rating;
	}

	public function set_content($content) {
		$this->content = $content;
	}

	public function set_post_id($post_id) {
		if (!$post_id or !get_post($post_id)) {
			throw new Exception('Post ID无效');
		}

		$this->post_id         = $post_id;
		$this->user_old_review = static::get_user_review($this->user_id, $this->post_id);
		$this->user_old_rating = $this->user_old_review ? get_comment_meta($this->user_old_review->comment_ID, 'rating', 1) : false;
		$this->rating_count    = wnd_get_post_meta($this->post_id, 'rating_count') ?: 0;
		$this->rating_avg      = get_post_meta($this->post_id, 'rating', 1);
	}

	public function rating() {
		if (!$this->post_id) {
			throw new Exception('Post ID无效');
		}

		if (!$this->rating) {
			throw new Exception('分值无效');
		}

		// 写入或更新当前用户review记录
		$this->insert_user_rating();

		// 更新被点评对象的review数据
		$this->update_post_rating();
	}

	/**
	 *写入或更新简短评论及用户点评分数
	 *@return int 写入或更新的comment ID
	 */
	protected function insert_user_rating() {
		$user_old_review_id = $this->user_old_review ? $this->user_old_review->comment_ID : 0;

		if ($user_old_review_id) {
			$comment_arr = [
				'comment_ID'      => $user_old_review_id,
				'comment_content' => $this->content,
				'user_id'         => $this->user_id,
			];
			wp_update_comment($comment_arr);
			$comment_id = $user_old_review_id;

		} else {
			$comment_arr = [
				'comment_post_ID'   => $this->post_id,
				'comment_content'   => $this->content,
				'comment_type'      => 'review',
				'user_id'           => $this->user_id,
				'comment_approved'  => 1,
				'comment_author_IP' => wnd_get_user_ip(),
			];
			$comment_id = wp_insert_comment($comment_arr);
		}

		update_comment_meta($comment_id, 'rating', $this->rating);
		return $comment_id;
	}

	/**
	 *更新被点评对象的post meta存储平均数据
	 *
	 */
	protected function update_post_rating() {
		/**
		 *
		 *首次/第一人，当前值即为平均值
		 */
		if (!$this->rating_count) {
			update_post_meta($this->post_id, 'rating', $this->rating);
			wnd_update_post_meta($this->post_id, 'rating_count', '1');
			return;
		}

		/**
		 *计算此次操作后的新平均数据 四舍五入取两位
		 *
		 *用户新增评价计算平均分 人数*平均分+本次分数 / 人数+1
		 *
		 *用户修改点评：人数*平均分+本次分-上次分数 / 人数
		 */
		if (!$this->user_old_rating) {
			$new_rating_count = $this->rating_count + 1;
			$new_rating_avg   = ($this->rating_count * $this->rating_avg + $this->rating) / $new_rating_count;

		} else {
			// 用户修改评价重新计算  人数*平均分+本次分-此前分数 / 人数
			$new_rating_count = $this->rating_count;
			$new_rating_avg   = ($this->rating_count * $this->rating_avg + $this->rating - $this->user_old_rating) / $new_rating_count;
		}

		// 更新点评平均数据
		update_post_meta($this->post_id, 'rating', $new_rating_avg);
		wnd_update_post_meta($this->post_id, 'rating_count', $new_rating_count);
	}

	/**
	 *
	 * 获取当前用户在当前产品的评价，若无，返回默认值 无打分：review_id = false  有打分没文章：review_id = 0 （int）
	 */
	public static function get_user_review($user_id, $post_id) {
		$args = [
			'post_id' => $post_id,
			'user_id' => $user_id,
			'type'    => 'review',
			'number'  => 1,
		];
		$comments_query = new WP_Comment_Query($args);
		$comments       = $comments_query->get_comments();

		return $comments ? $comments[0] : false;
	}

	public static function get_current_user_review($post_id) {
		return static::get_user_review(get_current_user_id(), $post_id);
	}

	// 获取当前产品的评测用户列表
	public static function get_review_users($post_id, $offset = 0, $limit = 10) {
		return;
	}

	// 获取用户评价过的产品列表
	public static function get_user_reviews($user_id, $offset = 0, $limit = 10) {
		return;
	}
}
