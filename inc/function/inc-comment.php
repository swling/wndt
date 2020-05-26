<?php

// ################################# 评论回复邮件通知

// 屏蔽WordPress评论通知后台设置改用自定义邮件
function wndt_notify_post_author_filter($maybe_notify, $comment_ID) {
	$maybe_notify = false;
	return $maybe_notify;
}
add_filter('notify_post_author', 'wndt_notify_post_author_filter', 11, 2);

// 插入评论时，发送邮件
add_action('comment_post', 'wndt_comment_mail_notify', 11, 3);
function wndt_comment_mail_notify($comment_id, $comment_approved, $commentdata) {

	//0、 当前评论
	$parent_comment_id = $commentdata['comment_parent'];
	$spam_confirmed    = $commentdata['comment_approved'];
	if ($spam_confirmed == 'spam') {
		return; // 垃圾评论、中止
	}

	//1、被评论文章
	$post_id     = $commentdata['comment_post_ID'];
	$post        = get_post($post_id);
	$post_title  = $post->post_title;
	$post_link   = get_permalink($post_id) . '#comment-' . $comment_id;
	$post_author = $post->post_author;

	///2、回复评论获取被评论作者邮箱
	if ($parent_comment_id) {

		$parent_comment         = get_comment($parent_comment_id);
		$parent_comment_user_id = $parent_comment->user_id;
		$parent_comment_content = '<p>您的留言：<br/>【' . $parent_comment->comment_content . '】</p>';

		if ($parent_comment_user_id) {

			$to = get_user_by('ID', $parent_comment_user_id)->user_email;

		} else {
			$to = $parent_comment->comment_author_email;
		}

		$to_user_name = $parent_comment->comment_author;

		//3、首层评论，获取文章作者有限
	} else {

		// 首层评论通知作者因此没有被回复的评论，
		$parent_comment_content = '';
		$to_user                = get_user_by('ID', $post_author);
		$to                     = $to_user->user_email;
		$to_user_name           = $to_user->display_name;

	}

	if (!$to or !is_email($to)) {
		return; // 没有邮箱地址或无效，中止
	}

	//3、邮件内容
	$subject = '您在 [' . get_option('blogname') . '] 收到了新回复';
	$message = '
	<div style="background-color:#eef2fa; border:1px solid #d8e3e8; color:#111; padding:0 15px; border-radius:5px;">
    	<p>' . $to_user_name . ', 您好！</p>
    	<p>您在《' . $post_title . '》收到了新回复</p>
    	' . $parent_comment_content . '
    	<p>' . $commentdata['comment_author'] . ' 给您的回复：<br />【' . $commentdata['comment_content'] . '】<br /></p>
    	<p><a href="' . $post_link . '" >点击查看详情</a>
     	<p>(此邮件由[' . get_option('blogname') . ']系统自动发送，请勿直接回复)</p>
	</div>';

	// 4、发送邮件
	$headers = "Content-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	wp_mail($to, $subject, $message, $headers);

}

//############################################################### 论坛话题被回复，更新主贴
// 直接通过的
function wndt_update_topic_when_comment($comment_ID, $comment_approved, $commentdata) {
	if (1 === $comment_approved) {
		$post_id = $commentdata['comment_post_ID'];

		if (get_post_type($post_id) == 'topic') {
			wp_update_post(['ID' => $post_id]);
		}
	}
}
add_action('comment_post', 'wndt_update_topic_when_comment', 11, 3);

// 审核通过的
add_action('comment_unapproved_to_approved', 'wndt_comment_approved', 11, 1);
function wndt_comment_approved($comment) {
	$post_id = $comment->comment_post_ID;
	if (get_post_type($post_id) == 'topic') {
		wp_update_post(['ID' => $post_id]);
	}
}
