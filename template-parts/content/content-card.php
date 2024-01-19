<main class="column is-9">
	<div class="main box" id="post-<?php the_ID(); ?>">
		<div class="content">
			<h1><?php the_title(); ?></h1>
			<p class="post-meta">发布于：<time><?php the_time('Y.m.d - G:i'); ?></time></p>
			<?php the_content(); ?>

			<?php
			// 产品购买
			echo '<h3>售价：' . wnd_get_post_price($post->ID) . '（库存：' . Wndt\Model\Wndt_Keys::get_keys_count($post->ID) . '）</h3>';
			echo Wndt\Module\Wndt_Order_Form::render(['post_id' => $post->ID, 'ajax' => true]);

			// 订单信息列表
			echo '<h3>我的 orders</h3>';
			Wndt\Model\Wndt_Keys::list_key_orders($post);
			?>
		</div>
	</div>
	<div class="box">
		<?php comments_template(); ?>
	</div>
</main>
<?php
get_sidebar('right');
