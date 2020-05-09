<?php

get_header();

// 自定义 taxonomy
if (is_tax()) {
	get_template_part('template-parts/archive/taxonomy', $taxonomy);

// 文章类型归档
} elseif (is_post_type_archive()) {
	include TEMPLATEPATH . '/template-parts/archive/post-type-archive.php';

//WordPress原生category、tag
} else {
	get_template_part('template-parts/archive/archive');
}

get_footer();
