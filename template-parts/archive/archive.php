<?php
use Wnd\View\Wnd_Filter;

global $taxonomy;

echo '<div class="column is-paddingless">';
$filter = new Wnd_Filter(false);
$filter->add_post_type_filter(get_taxonomy($taxonomy)->object_type);

if ($taxonomy == $filter->category_taxonomy) {
	$filter->add_tags_filter();
}

$filter->set_post_template('wndt_post_list_tpl');
// $filter->set_ajax_container("#filter-results");
$filter->query();

echo $filter->get_tabs();

echo '<div class="columns">';
echo '<div id="filter-results" class="column">';
echo $filter->get_results();
echo '</div>';
get_sidebar('right');
echo '</div>';
echo '</div>';
