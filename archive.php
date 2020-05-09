<?php

get_header();

$taxonomy = get_queried_object()->taxonomy ?? false;

// Term 归档
if ($taxonomy) {
	get_template_part('template-parts/archive/archive', $taxonomy);
}

// 自定义 post type 归档
elseif (is_post_type_archive()) {
	get_template_part('template-parts/archive/archive-postype', get_post_type());

}

// 默认 归档
else {
	get_template_part('template-parts/archive/archive');
}

get_footer();
