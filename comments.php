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
		<?php if (comments_open()) : ?>
			<?php if ($post_type == 'post') { ?>
				<h3 class="comments-heading"><?php comments_number(__('<a href="#comments">写一条评论</a>', 'hacker'), __('<a href="#comments">1 条评论</a>', 'hacker'), __('<a href="#comments">% 条评论</a>', 'hacker')); ?></h3>
			<?php } elseif ($post_type == 'topic') { ?>
				<h3 class="comments-heading"><?php comments_number(__('<a href="#comments">回帖</a>', 'hacker'), __('<a href="#comments">1 条回帖</a>', 'hacker'), __('<a href="#comments">% 条回帖</a>', 'hacker')); ?></h3>
			<?php } ?>
		<?php else : ?>
			<h3 class="comments-heading">评论关闭</h3>
		<?php endif; ?>
	</header>
	<?php
	if ($post_type == 'topic') {
		$comment_field = '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" maxlength="1000" required="required" placeholder="回帖"></textarea></p>';
	} else {
		$comment_field = '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" maxlength="1000" required="required" placeholder="评论"></textarea></p>';
	}

	$comments_args = [
		'must_log_in'   => '<p class="must-log-in center notice"><a onclick="wnd_ajax_modal(\'wnd_user_center\')" >需登录后评论</a></p>',
		'comment_field' => $comment_field,
		'logged_in_as'  => '<p class="logged-in-as">已登录为：' . $user_identity . '</p>',
		'submit_button' => '<button class="button is-"' . wnd_get_config('primary_color') . '>提交</button>',
	];

	comment_form($comments_args);
	?>
	<!-- END .Comments-area__header -->
	<?php if (have_comments()) : ?>
		<ol class="comment-list">
			<?php wp_list_comments('avatar_size=48'); ?>
		</ol>
		<?php if (get_comment_pages_count() > 1 and get_option('page_comments')) : // are there comments to navigate through
				?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<div class="nav-previous alignleft"><?php previous_comments_link(); ?></div>
				<div class="nav-next alignright"><?php next_comments_link(); ?></div>
			</nav><!-- #comment-nav-below -->
		<?php endif; // check for comment navigation
			?>

	<?php endif; // have_comments()
	?>
</div>
<!-- END #comments -->