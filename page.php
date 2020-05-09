<?php get_header(); ?>
<?php get_sidebar('left'); ?>
<div id="content" class="column">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post content box narrow-container">
		<h1 id="posttitle">
			<?php the_title(); ?>
		</h1>
		<?php the_content(); ?>
		<?php wp_link_pages(); ?>
	</div>
	<?php endwhile; endif; ?>
	<?php comments_template(); ?>
</div>
<?php get_footer(); ?>