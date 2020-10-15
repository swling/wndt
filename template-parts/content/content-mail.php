<main class="column content">
	<div class="columns">
		<div class="column">
			<div class="box">
				<div class="columns">
					<div class="column">
						<?php
						$html = '<h1 class="is-size-4 is-size-6-mobile"><span class="icon">';
						$html .= '<i class="fa fa-envelope"></i></span>&nbsp' . $post->post_title;
						$html .= '</h1>';
						$html .= '<div><span class="icon"><i class="far fa-clock"></i></span>' . $post->post_date . '</div>';
						echo $html;
						?>
					</div>
				</div>
				<article class="entry">
					<?php echo $post->post_content;	?>
				</article>
			</div>
		</div>
	</div>
</main>
<?php
// 更新阅读状态
if ('wnd-unread' == $post->post_status) {
	$post_array = [
		'ID'          => $post->ID,
		'post_status' => 'wnd-read',
	];
	wp_update_post($post_array);
}
