<?php
$curauth    = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
$user_id    = $curauth->ID;
$user_data  = get_userdata($user_id);
$user_roles = $user_data->roles[0];
//array of roles the user is part of.
// 当前主页
$current_author_url = get_author_posts_url($user_id);
// $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
get_header();

echo '<div class="column">';
$filter = new Wnd\View\Wnd_Filter(true);
$filter->add_post_type_filter(['supply', 'demand']);
$filter->add_query(['author' => $user_id]);
$filter->set_post_template('wndt_post_list_tpl');
$filter->set_ajax_container("#filter-results");
$filter->query();

echo $filter->get_tabs();

echo '<div class="columns is-marginless">';
echo '<div id="filter-results" class="column content">';
echo $filter->get_results();
echo '</div>';
echo '</div>';

echo '</div>';

get_footer();
