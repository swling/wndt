<main class="column is-9">
	<div class="main box" id="post-<?php the_ID(); ?>">
		<div class="content">
			<h1><?php the_title(); ?></h1>
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php
			/**
			 * 在内容页面判断当前用户是否已付费
			 * 采用wp editor <!--more--> 标记区分免费部分与付费部分
			 */
			$with_paid_content = false;
			if (wnd_get_post_price($post->ID)) {
				$content           = wnd_explode_post_by_more($post->post_content);
				$with_paid_content = $content[1] ?? false;
				$user_id           = get_current_user_id();
				if (wnd_user_has_paid($user_id, $post->ID) or $post->post_author == $user_id) {
					the_content();
				} else {
					echo $content[0];
				}
			} else {
				the_content();
			}

			// 在内容页放置付费按钮，将自动检测是否包含付费文件
			echo wnd_pay_button($post, $with_paid_content);
			?>
		</div>
	</div>
	<div class="box">
		<?php comments_template(); ?>
	</div>
</main>
<?php
get_sidebar('right');
