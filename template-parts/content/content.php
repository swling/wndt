<main class="column">
	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
		<h1 class="title"><?php the_title(); ?></h1>
		<div class="content">
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php
			the_content();
			echo wnd_gallery($post->ID, 154, 154);

			// 在内容页面判断当前用户是否已付费
			if (wnd_user_has_paid(get_current_user_id(), $post->ID)) {
				echo "仅对付费用户展示的内容";
			} else {
				echo "仅对未付费用户展示的内容";
			}

			// 在内容页放置按钮
			echo wnd_paid_reading_button($post->ID);
			// echo wnd_paid_download_button($post->ID);
			?>
			<?php comments_template(); ?>
		</div>
	</div>
</main>
<?php
// get_sidebar('right');
