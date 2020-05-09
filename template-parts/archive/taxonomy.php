<?php
use Wnd\View\Wnd_Filter;

get_header();

// 翻页
// $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;//分页
// $paged          = (isset($_GET['pages'])) ? intval($_GET['pages']) : 1;
// $posts_per_page = get_option('posts_per_page');

// 获取当前main query term信息 用于填写wp query 默认值
$queried_object = get_queried_object();
$term_id        = $queried_object->term_id;
$taxonomy       = $queried_object->taxonomy;
$term_count     = $queried_object->count;

$tax_query = [
	'relation' => 'AND',
	[
		'taxonomy' => $taxonomy,
		'field'    => 'term_id',
		'terms'    => $term_id,
	],
];

echo '<div class="column">';

$filter = new Wnd_Filter();
$filter->add_post_type_filter(get_taxonomy($taxonomy)->object_type);

$filter->add_query(['tax_query' => $tax_query]);
$filter->add_taxonomy_filter(['taxonomy' => 'region', 'orderby' => 'count', 'order' => 'DESC']);
if ($taxonomy == $filter->category_taxonomy) {
	$filter->add_related_tags_filter();
}
$filter->set_post_template('wndt_post_list_tpl');
$filter->set_ajax_container("#filter-results");
$filter->query();

echo $filter->get_tabs();

echo '<div class="columns is-marginless">';
echo '<div id="filter-results" class="column">';
echo $filter->get_results();
echo '</div>';
get_sidebar('right');
echo '</div>';
echo '</div>';
get_footer();
