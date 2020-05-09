<?php
// 默认页面展示模板，仅当请求的内容未找到任何匹配时，调用本文件
get_header();
?>
<div id="main" class="column" role="main">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				<h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
				<div class="entry">
					<p>未定义的内容</p>
				</div>
			</div>
		<?php endwhile; ?>
	<?php else : ?>
		<p>未找到相关内容</p>
	<?php endif; ?>
</div>
<?php
get_footer();
