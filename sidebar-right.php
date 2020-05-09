<div id="right-sidebar" class="sidebar column is-3">
	<!-- <img src="https://tpc.googlesyndication.com/simgad/688618643326598743?sqp=4sqPyQQ7QjkqNxABHQAAtEIgASgBMAk4A0DwkwlYAWBfcAKAAQGIAQGdAQAAgD-oAQGwAYCt4gS4AV_FAS2ynT4&rs=AOga4qmFbhPMHZwsGb4CmlKL5If4AtAbFA" alt=""> -->
	<?php
$queried_object = get_queried_object();
// 分类时显示
if (is_tax()) {

	// 获取当前main query term信息 用于填写wp query 默认值
	$term_id = get_queried_object()->term_id;
	// $taxonomy = get_queried_object()->taxonomy; // taxonomy 页面似乎自带了全局变量 $taxonomy 无需定义
	$term_count = get_queried_object()->count;
} else if (is_singular($post_types ='supply')) {

	$profile_id = wndt_get_user_profile_id($post->post_author);

	echo '<div class="has-text-centered">' .
	'<div class="is-divider" data-content="公司信息"></div>' .
	wndt_company_profile($profile_id, $avatar_size = 100) .
		'</div>';
}

// echo wndt_shoutcut_box();

?>
<?php
if (is_archive() and 'company' == $queried_object->name) {
	$args = [
		'post_type'      => 'company',
		'posts_per_page' => 5,
		'no_found_rows'  => true,
	];

	$query = new WP_Query($args);
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			echo wndt_post_list_tpl($post, true);
		}
		wp_reset_postdata(); //重置查询
	}

} else {
	$args = [
		'post_type'      => 'supply',
		'posts_per_page' => 5,
		'no_found_rows'  => true,
	];

	$query = new WP_Query($args);
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			echo wndt_post_list_tpl($post, true);
		}
		wp_reset_postdata(); //重置查询
	}
}
?>
</div>