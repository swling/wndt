<?php
/*
 *############################################################################################# WordPress原生filter
 */

/**
 * Search SQL filter for matching against post title only.
 *
 * @link    http://wordpress.stackexchange.com/a/11826/1685
 *
 * @param   string      $search
 * @param   WP_Query    $wp_query
 */
// add_filter('posts_search', 'wndt_search_by_title', 11, 2);
function wndt_search_by_title($search, $wp_query) {
	if (!empty($search) and !empty($wp_query->query_vars['search_terms'])) {
		global $wpdb;

		$q      = $wp_query->query_vars;
		$n      = !empty($q['exact']) ? '' : '%';
		$search = [];
		foreach ((array) $q['search_terms'] as $term) {
			$search[] = $wpdb->prepare("$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like($term) . $n);
		}

		if (!is_user_logged_in()) {
			$search[] = "$wpdb->posts.post_password = ''";
		}

		$search = ' AND ' . implode(' AND ', $search);
	}

	return $search;
}
