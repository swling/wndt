<main class="column is-9">
	<div class="main box" id="post-<?php the_ID(); ?>">
		<div class="content">
			<h1><?php the_title(); ?></h1>
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php
			the_content();

			// 产品购买
			echo Wnd\Module\Wnd_Order_Form::render(['post_id' => $post->ID, 'ajax' => false]);
			?>
		</div>
	</div>
	<div class="box">
		<?php comments_template(); ?>
	</div>
</main>
<?php
get_sidebar('right');
