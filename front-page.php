<?php get_header();?>
<div id="main" class="column columns content is-multiline">
	<div class="column">
		<?php
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				echo wndt_post_list_tpl($post);
			}
		} 
		?>
	</div>
</div>
<?php
get_sidebar('right');
get_footer();
