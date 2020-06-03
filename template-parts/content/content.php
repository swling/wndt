<main class="column is-9">
	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
		<div class="content">
			<h1><?php the_title(); ?></h1>
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php
			/**
			 * 在内容页面判断当前用户是否已付费
			 * 采用wp editor <!--more--> 标记区分免费部分与付费部分
			 */
			if (wnd_get_post_price($post->ID)) {
				$user_id = get_current_user_id();
				if (wnd_user_has_paid($user_id, $post->ID) or $post->post_author == $user_id) {
					the_content();;
				} else {
					$content = wndt_explode_post_by_more($post->post_content);
					echo $content[0];
				}
			} else {
				the_content();
			}

			// 在内容页放置按钮
			echo wnd_paid_reading_button($post->ID);
			echo wnd_paid_download_button($post->ID);
			?>
		</div>
	</div>
	<?php comments_template(); ?>
</main>
<?php
get_sidebar('right');
