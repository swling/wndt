<?php
namespace Wnd\Model;

/**
 *@since 2019.09.27
 *万能的WordPress
 *
 *@since 2018.07
 *主要功能：
 *1、关联文章的分类和标签，获取当前分类下，文章所携带的标签、获取指定 taxonomy下所有分类，及各个分类对应的标签
 *2、单独数据表实现，增删term时，及删除文章时，实现同步更新
 *
 *自定义分类法约定taxonomy：分类为 $post_type_cat 标签为：$post_type_tag
 */
class Wnd_Tag_Under_Category {

	/**
	 *Hook
	 */
	public static function add_hook() {
		add_action('set_object_terms', [__CLASS__, 'monitor_object_terms_changes'], 10, 6);
		add_action('before_delete_post', [__CLASS__, 'update_tag_under_category_when_post_delete'], 10, 1);
		add_action('pre_delete_term', [__CLASS__, 'delete_term'], 10, 2);
	}

	/**
	 *监听文章分类及标签更新
	 */
	public static function monitor_object_terms_changes($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
		$post_type    = get_post_type($object_id);
		$cat_taxonomy = ($post_type == 'post') ? 'category' : $post_type . '_cat';
		$tag_taxonomy = $post_type . '_tag';

		// taxonomy合法性检测
		if (!taxonomy_exists($cat_taxonomy) or !taxonomy_exists($tag_taxonomy)) {
			return;
		}

		// 1、分类改变
		if ($taxonomy == $cat_taxonomy) {
			// 获取当前修改文章的指定标签
			$tags = get_the_terms($object_id, $tag_taxonomy);
			if (!$tags) {
				return;
			}

			foreach ($tags as $tag) {
				$tag_id = $tag->term_id;

				// 旧数据 递减
				$delete_tt_ids = array_diff($old_tt_ids, $tt_ids);
				if ($delete_tt_ids) {
					foreach ($delete_tt_ids as $cat_id) {
						static::update_tag_under_category($cat_id, $tag_id, $tag_taxonomy, false);
					}
					unset($cat_id);
				}

				// 新数据 递增
				$add_tt_ids = array_diff($tt_ids, $old_tt_ids);
				if ($add_tt_ids) {
					foreach ($add_tt_ids as $cat_id) {
						static::update_tag_under_category($cat_id, $tag_id, $tag_taxonomy, true);
					}
					unset($cat_id);
				}
			}
			unset($tag);

			// 2、标签改变
		} elseif ($taxonomy == $tag_taxonomy) {
			// 获取当前文章的分类
			$cats = get_the_terms($object_id, $cat_taxonomy);
			if (!$cats) {
				return;
			}

			foreach ($cats as $cat) {
				$cat_id = $cat->term_id;

				// 旧数据 递减
				$delete_tt_ids = array_diff($old_tt_ids, $tt_ids);
				if ($delete_tt_ids) {
					foreach ($delete_tt_ids as $tag_id) {
						static::update_tag_under_category($cat_id, $tag_id, $taxonomy, false);
					}
					unset($tag_id);
				}

				// 新数据 递减
				$add_tt_ids = array_diff($tt_ids, $old_tt_ids);
				if ($add_tt_ids) {
					foreach ($add_tt_ids as $tag_id) {
						static::update_tag_under_category($cat_id, $tag_id, $taxonomy, true);
					}
					unset($tag_id);
				}
			}
			unset($cat);
		}
	}

	/**
	 *删除文章时：更新tag under category数据
	 */
	public static function update_tag_under_category_when_post_delete($object_id) {
		$post_type    = get_post_type($object_id);
		$cat_taxonomy = ($post_type == 'post') ? 'category' : $post_type . '_cat';
		$tag_taxonomy = $post_type . '_tag';

		$cats = get_the_terms($object_id, $cat_taxonomy);
		if (!$cats or is_wp_error($cats)) {
			return;
		}

		$tags = get_the_terms($object_id, $tag_taxonomy);
		if (!$tags or is_wp_error($tags)) {
			return;
		}

		foreach ($cats as $cat) {
			$cat_id = $cat->term_id;
			if ($cat_id) {
				foreach ($tags as $tag) {
					$tag_id = $tag->term_id;
					static::update_tag_under_category($cat_id, $tag_id, $tag_taxonomy, false);
				}
				unset($tag);
			}
		}
		unset($cat);
	}

	/**
	 *删除term时，更新 tag under category数据
	 */
	public static function delete_term($term_id, $taxonomy) {
		global $wpdb;

		/**
		 *删除标签
		 */
		if (strpos($taxonomy, '_tag')) {
			// 删除对象缓存
			$cat_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT cat_id FROM $wpdb->wnd_terms WHERE tag_id = %d",
					$term_id
				)
			);
			if ($cat_ids) {
				foreach ($cat_ids as $cat_id) {
					wp_cache_delete($cat_id . $taxonomy, 'wnd_tags_under_category');
				}unset($cat_ids, $cat_id);
			}

			// 删除记录
			$wpdb->delete($wpdb->wnd_terms, ['tag_id' => $term_id]);

			/**
			 *删除分类
			 */
		} else {
			// 删除对象缓存
			$tag_taxonomies = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT tag_taxonomy FROM $wpdb->wnd_terms WHERE cat_id = %d",
					$term_id
				)
			);
			if ($tag_taxonomies) {
				foreach ($tag_taxonomies as $tag_taxonomy) {
					wp_cache_delete($term_id . $tag_taxonomy, 'wnd_tags_under_category');
				}unset($tag_taxonomies, $tag_taxonomy);
			}

			// 删除记录
			$wpdb->delete($wpdb->wnd_terms, ['cat_id' => $term_id]);
		}
	}

	/**
	 *写入标签和分类数据库
	 */
	protected static function update_tag_under_category($cat_id, $tag_id, $tag_taxonomy, $inc) {
		global $wpdb;

		// 删除对象缓存
		wp_cache_delete($cat_id . $tag_taxonomy, 'wnd_tags_under_category');

		$result = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $wpdb->wnd_terms WHERE cat_id = %d AND tag_id = %d ",
			$cat_id,
			$tag_id
		));

		// 更新
		if ($result) {
			$ID    = $result->ID;
			$count = $inc ? $result->count + 1 : $result->count - 1;

			// count为0，删除记录 返回
			if (!$count) {
				$wpdb->delete($wpdb->wnd_terms, ['ID' => $ID]);
				return true;
			}

			$do_sql = $wpdb->update(
				$wpdb->wnd_terms, //table
				['count' => $count], // data
				['ID' => $ID], // where
				['%d'], //data format
				['%d'] //where format
			);

			//没有记录，且操作为新增，写入数据
		} elseif ($inc) {
			$do_sql = $wpdb->insert(
				$wpdb->wnd_terms,
				['cat_id' => $cat_id, 'tag_id' => $tag_id, 'tag_taxonomy' => $tag_taxonomy, 'count' => 1], //data
				['%d', '%d', '%s', '%d'] // data format
			);

			//没有记录无需操作
		} else {
			return false;
		}

		// 返回数据操作结果
		return $do_sql;
	}

	/**
	 *@since 2019.03.24 获取标签数据
	 *@param int 	$cat_id
	 *@param string $tag_taxonomy
	 *@param int 	$milit
	 */
	public static function get_tags($cat_id, $tag_taxonomy, $limit = 50) {
		// 获取缓存
		$tags = wp_cache_get($cat_id . $tag_taxonomy, 'wnd_tags_under_category');
		$max  = 500;

		// 缓存无效
		if (false === $tags) {
			global $wpdb;
			// 一个分类下可能对应多个tag类型此处区分
			if ('any' == $tag_taxonomy) {
				$tags = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM $wpdb->wnd_terms WHERE cat_id = %d ORDER BY count DESC LIMIT %d",
						$cat_id,
						$max
					)
				);
			} else {
				$tags = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM $wpdb->wnd_terms WHERE cat_id = %d AND tag_taxonomy = %s ORDER BY count DESC LIMIT %d",
						$cat_id,
						$tag_taxonomy,
						$max
					)
				);
			}

			// 缓存查询结果
			wp_cache_set($cat_id . $tag_taxonomy, $tags, 'wnd_tags_under_category', 86400);
		}

		return array_slice($tags, 0, $limit);
	}
}
