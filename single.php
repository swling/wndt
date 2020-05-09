<?php
get_header();

while (have_posts()) : the_post();
	get_template_part('template-parts/content/content', get_post_type());
endwhile;

// 页脚
// get_sidebar('right');
get_footer();
