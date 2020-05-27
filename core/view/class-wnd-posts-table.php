<?php
namespace Wnd\View;

use WP_Query;

/**
 *@since 2019.03.13
 *@param object		$query 				WP_Query对象
 *@param bool 		$show_edit 			是否显示编辑链接
 *@param bool 		$show_preview 		是否显示编辑链接
 */
class Wnd_Posts_Table {
	protected $columns = [];
	protected $query;
	protected $show_preview;
	protected $show_edit;
	protected static $defaults = [
		'post_field' => '',
		'title'      => null,
		'content'    => null,
		'class'      => null,
	];
	public $html;

	/**
	 *构造函数
	 */
	public function __construct(WP_Query $query, bool $show_edit = false, bool $show_preview = false) {
		$this->query        = $query;
		$this->show_preview = $show_preview;
		$this->show_edit    = $show_edit;
	}

	/**
	 *新增表单列
	 *
	 */
	public function add_column($column = []) {
		$column          = array_merge(static::$defaults, $column);
		$this->columns[] = $column;
	}

	/**
	 *
	 *构造表单列表
	 */
	public function build() {
		// 表单开始
		$this->html = '<table class="table is-fullwidth is-hoverable is-striped">';

		// 表头
		$this->html .= '<thead>';
		$this->html .= '<tr>';
		foreach ($this->columns as $column) {
			$this->html .= '<th' . $this->get_the_class($column) . '>' . $column['title'] . '</th>';
		}
		unset($column);

		if ($this->show_edit or $this->show_preview) {
			$this->html .= '<td class="is-narrow">';
			$this->html .= '操作';
			$this->html .= '</td>';
		}
		$this->html .= '</tr>';
		$this->html .= '</thead>';

		// 列表
		$this->html .= '<tbody>';
		while ($this->query->have_posts()) {
			$this->query->the_post();
			global $post;
			$this->html .= '<tr>';

			// 读取并构建列
			foreach ($this->columns as $column) {
				if ('post_title_with_link' == $column['post_field']) {
					$content = '<a href="' . get_permalink() . '" target="_blank">' . $post->post_title . (wnd_is_revision($post->ID) ? '[revision]' : '') . '</a>';
					$this->html .= '<td' . $this->get_the_class($column) . '>' . $content . '</td>';
					continue;

				}

				if ('post_date' == $column['post_field']) {
					$content = get_the_date('y-m-d H:i');
					$this->html .= '<td' . $this->get_the_class($column) . '>' . $content . '</td>';
					continue;
				}

				if ('post_author' == $column['post_field']) {
					$content = '<a href="' . get_author_posts_url($post->post_author) . '">' . get_userdata($post->post_author)->display_name . '</a>';
					$this->html .= '<td' . $this->get_the_class($column) . '>' . $content . '</td>';
					continue;
				}

				if ('post_parent_with_link' == $column['post_field']) {
					if ($post->post_parent) {
						$parent_post = get_post($post->post_parent);
						$content     = '<a href="' . get_permalink($post->post_parent) . '" target="_blank">' . $parent_post->post_title . '</a>';
					} else {
						$content = $post->post_title;
					}

					$this->html .= '<td' . $this->get_the_class($column) . '>' . $content . '</td>';
					continue;
				}

				if (in_array($post->post_type, ['order', 'recharge', 'stats-re', 'stats-ex']) and 'post_content' == $column['post_field']) {
					$this->html .= '<td' . $this->get_the_class($column) . '>' . number_format((float) $post->post_content, 2) . '</td>';
					continue;
				}

				$content = $column['content'] ?: get_post_field($column['post_field']);
				$this->html .= '<td' . $this->get_the_class($column) . '>' . $content . '</td>';
			}
			unset($column);

			// 编辑管理
			if ($this->show_edit or $this->show_preview) {
				$this->html .= '<td class="is-narrow has-text-centered">';
				$this->html .= $this->show_preview ? '<a onclick="wnd_ajax_modal(\'wnd_post_info\',\'' . get_the_ID() . '\')"> <i class="fas fa-info-circle"></i> </a>' : '';
				$this->html .= $this->show_edit ? '<a onclick="wnd_ajax_modal(\'wnd_post_status_form\',\'' . get_the_ID() . '\')"> <i class="fas fa-cog"></i> </a>' : '';
				$this->html .= '</td>';
			}

			$this->html .= '</tr>';
		}
		wp_reset_postdata();
		$this->html .= '</tbody>';

		// 表单结束
		$this->html .= '</table>';
	}

	/**
	 *获取column class
	 **/
	protected function get_the_class($column) {
		if ($column['class'] ?? false) {
			return ' class="' . $column['class'] . '"';
		}
	}
}
