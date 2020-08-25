<?php
get_header();

while (have_posts()): the_post();
	get_template_part('template-parts/content/content', get_post_type());
endwhile;

// get_sidebar('right');

// 页脚
get_footer();
