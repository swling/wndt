<?php
$user_data = get_queried_object()->data;
$user_id   = $user_data->ID;

get_header();
?>
<main class="column has-text-centered" style="margin-top:1.5rem;">
	<div class="columns">
		<div class="column has-text-centered content">
			<div id="user-avatar">
				<?php echo get_avatar($user_id, '100'); ?>
			</div>
			<?php if (!empty($user_data->user_url)) { ?>
				<h3 class="center">博客：<a href="<?php echo $user_data->user_url; ?>" target="_blank" rel="nofollow">@<?php wp_title(''); ?></a></h3>
			<?php } ?>
			<div id="user-description">
				<?php echo get_user_meta($user_id, 'description', 1); ?>
			</div>
			<?php
			if (wnd_is_manager()) {
				echo '<h3>管理</h3>';
				echo wnd_modal_button('删除用户', 'wnd_delete_user_form', ['user_id' => $user_id], 'is-danger is-outlined is-small');
				echo '&nbsp;' . wnd_modal_button('封禁用户', 'wnd_account_status_form', ['user_id' => $user_id], 'is-danger is-outlined is-small');
			}
			?>
		</div>
	</div>
</main>
<?php
get_footer();
