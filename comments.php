<?php

/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Hacker
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if (post_password_required()) {
	return;
}

$post_type     = $post->post_type;
$user          = wp_get_current_user();
$user_identity = $user->exists() ? $user->display_name : '';
?>
<div id="comments" class="comments">
	<header class="comments-header">
		<?php if (comments_open()) {
		?>
			<?php if ($post_type != 'topic') { ?>
				<h3 class="comments-heading">
					<a href="#comments">
						<?php comments_number(
							__('撰写评论', 'wndt'),
							__('1 条评论', 'wndt'),
							__('% 条评论', 'wndt')
						); ?>
					</a>
				</h3>
			<?php } else {
			?>
				<h3 class="comments-heading">
					<a href="#comments">
						<?php comments_number(
							__('撰写回帖', 'wndt'),
							__('1 条回帖', 'wndt'),
							__('% 条回帖', 'wndt')
						); ?>
					</a>
				</h3>
			<?php } ?>
		<?php } else { ?>
			<h3 class="comments-heading"><?php _e('Comments are closed.'); ?></h3>
		<?php } ?>
	</header>
	<?php
	if ($post_type != 'topic') {
		$comment_field = '<textarea id="comment" name="comment" maxlength="1000" required="required" placeholder="' . __('评论', 'wndt') . '"></textarea>';
	} else {
		$comment_field = '<textarea id="comment" name="comment" maxlength="1000" required="required" placeholder="' . __('回帖', 'wndt') . '"></textarea>';
	}

	$comments_args = [
		'must_log_in'   => '<div class="has-text-centered"><a onclick="wnd_ajax_modal(\'wnd_user_center\')">' . __('登录', 'wndt') . '</a></div>',
		'comment_field' => $comment_field,
		'logged_in_as'  => '<p class="logged-in-as">logged is as：' . $user_identity . '</p>',
		'submit_button' => '<button class="button is-' . wnd_get_config('primary_color') . '">提交</button>',
	];
	if (is_user_logged_in() and !$user->user_email) {
		echo '<div class="has-text-centered content">';
		echo wnd_modal_button(__('请绑定邮箱后评论', 'wndt'), 'wnd_bind_email_form', '', 'is-danger');
		echo '</div>';
	} else {
		comment_form($comments_args);
	}
	?>
	<!-- END .Comments-area__header -->
	<?php if (have_comments()) : ?>
		<ol class="comment-list">
			<?php wp_list_comments('avatar_size=48'); ?>
		</ol>
		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<div class="nav-previous alignleft"><?php previous_comments_link(); ?></div>
				<div class="nav-next alignright"><?php next_comments_link(); ?></div>
			</nav><!-- #comment-nav-below -->
		<?php endif; ?>

	<?php endif; ?>
</div>
<!-- END #comments -->