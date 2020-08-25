<?php
use Wnd\View\Wnd_Filter;

get_header();

// 获取当前main query term信息 用于填写wp query 默认值
$queried_object = get_queried_object();

// 指定默认值，暂无法适配 wnd filter 仅为预备代码
$tax_query = [
	'relation' => 'AND',
	[
		'taxonomy' => 'attribute',
		'field'    => 'slug',
		'terms'    => 'company',
	],
];

echo '<div class="column is-paddingless">';
$filter = new Wnd_Filter();
$filter->add_post_type_filter([$queried_object->name]);

$filter->add_taxonomy_filter(['taxonomy' => $filter->category_taxonomy, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false]);
$filter->add_taxonomy_filter(['taxonomy' => 'region', 'orderby' => 'count', 'order' => 'DESC']);
$filter->add_related_tags_filter();

if ($queried_object->name == 'supply') {
	// $filter->add_query(['tax_query' => $tax_query]);
	$filter->add_taxonomy_filter(['taxonomy' => 'attribute', 'orderby' => 'name', 'order' => 'DESC']);
}

if ($queried_object->name == 'demand') {
	$filter->add_query(['post_status' => ['publish', 'wnd-closed']]);
	$filter->add_post_status_filter(['进行中' => 'publish', '已结束' => 'wnd-closed']);
} else {
	$filter->add_query(['post_status' => 'publish']);
}

$filter->set_post_template('wndt_post_list_tpl');
$filter->set_posts_per_page(get_option('posts_per_page'));
$filter->set_ajax_container("#filter-results");
$filter->query();

echo $filter->get_tabs();

echo '<div class="columns">';
echo '<div id="filter-results" class="column">';
echo $filter->get_results();
echo '</div>';
get_sidebar('right');
echo '</div>';
echo '</div>';

get_footer();
