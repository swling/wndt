<?php get_header(); ?>
<div id="main" class="column">
	<div class="columns">
		<div class="box column">
			<?php echo Wnd\Module\Common\Wnd_Search_Form::render(); ?>
		</div>
	</div>
	<hr>
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<?php echo wndt_post_list_tpl($post); ?>
		<?php endwhile; ?>
		<div class="pagination is-centered">
			<ul class="pagination-list">
				<li><?php next_posts_link('下一页'); ?></li>
				<li><?php previous_posts_link('上一页'); ?></li>
			</ul>
		</div>
	<?php else : ?>
		<div class="message">
			<div class="message-body">没有找到符合要求的内容！</div>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
