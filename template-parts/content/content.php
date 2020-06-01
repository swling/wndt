<main class="column">
	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
		<h1 class="title"><?php the_title(); ?></h1>
		<div class="content">
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php
			// 在内容页面判断当前用户是否已付费
			if (wnd_get_post_price($post->ID)) {
				if (wnd_user_has_paid(get_current_user_id(), $post->ID)) {
					the_content();
				} else {
					the_excerpt();
				}
			} else {
				the_content();
			}

			// 在内容页放置按钮
			echo wnd_paid_reading_button($post->ID);
			echo wnd_paid_download_button($post->ID);
			?>
			<?php comments_template(); ?>
		</div>
	</div>
</main>
<?php
// get_sidebar('right');
