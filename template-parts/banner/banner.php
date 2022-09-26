<?php

/**
 *@since 2019.1.5
 *大横幅
 */

echo Wndt\Module\Wndt_Notification::render();

//1、 home
if (is_home()) {
?>
	<section id="banner" class="hero is-light is-hidden-mobile">
		<div class="hero-body is-danger">
			<div class="container content columns is-vcentered">
				<div class="column">
					<h1 class="title is-spaced"><?php bloginfo('name'); ?></h1>
					<h2 class="subtitle">
						<?php bloginfo('description'); ?>
					</h2>
					<a class="button is-black" onclick="wnd_ajax_modal('user/wnd_user_center')">免费注册</a>
					<a class="button is-danger is-outlined" onclick="wnd_ajax_modal('user/wnd_login_form')">立即登录</a>
				</div>
			</div>
		</div>
	</section>
<?php

} elseif (is_singular('company')) {

	$profile_id = wndt_get_user_profile_id($post->post_author);
	// 排除管理员添加的
	$profile_id = ($post->post_author == 1) ? $post->ID : $profile_id;
	$phone      = wnd_get_post_meta($profile_id, 'phone');
	$website    = esc_url(wnd_get_post_meta($profile_id, 'website'));
	$address    = wnd_get_post_meta($profile_id, 'address');
	$last_login = date('Y-m-d', wnd_get_user_meta($post->post_author, 'last_login'));

?>
	<section id="banner" class="hero">
		<div class="hero-body">
			<div class="container">
				<div class="columns">
					<div class="column">
						<div class="banner">
							<div class="columns is-size-7-mobile">
								<div class="column is-narrow has-text-centered">
									<?php echo wndt_post_thumbnail($post->ID, '150', '150'); ?>
								</div>
								<div class="column">
									<?php
									$html = '<h1 class="is-size-6-mobile is-size-5">';
									// $html .= '<span class="icon"><i class="fa fa-building"></i></span>&nbsp;';
									$html .= !wnd_is_manager($post->post_author) ? get_user_by('ID', $post->post_author)->display_name : $post->post_title;
									$html .= wndt_cert_icon($post->post_author);
									$html .= '</h1>';
									$html .= '<div class="is-hidden-mobile"><span class="icon"><i class="fas fa-map-marker-alt"></i></span>' . get_the_term_list($post->ID, 'region', $before = '', $sep = '', $after = ' ') . wnd_get_post_meta($post->ID, 'address') . '</div>';
									$html .= '<div class="is-hidden-mobile"><span class="icon"><i class="fas fa-link"></i></span><a href ="' . $website . '">' . $website . '</a></div>';
									$html .= '<div class="tags">' . get_the_term_list($post->ID, $post->post_type . '_tag', '<span class="icon"><i class="fas fa-hashtag"></i></span>', '<span class="icon"><i class="fas fa-hashtag"></i></span>', '') . '</div>';
									$html .= '<div class="content">' . wnd_trim_words($post->post_excerpt, 100) . '</div>';
									echo $html;
									?>
								</div>
								<div class="column is-narrow has-text-centered">
									<?php echo wnd_modal_button('联系我', 'wndt_contact_info', ['post_id' => $post->ID], 'is-outlined is-small is-' . wnd_get_config('primary_color')); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php

} elseif (is_singular('people')) {

	$profile_id = wndt_get_user_profile_id($post->post_author);
	// 排除管理员添加的
	$profile_id = ($post->post_author == 1) ? $post->ID : $profile_id;
	$phone      = wnd_get_post_meta($profile_id, 'phone');
	$last_login = date('Y-m-d', wnd_get_user_meta($post->post_author, 'last_login'));

?>
	<section id="banner" class="hero">
		<div class="hero-body">
			<div class="container">
				<div class="columns">
					<div class="column">
						<div class="content has-text-centered">
							<?php
							$html = wndt_post_thumbnail($post->ID, '100', '100');

							$html .= '<div class="banner">';
							$html .= '<h1 class="is-size-5 is-size-6-mobile">';
							$html .= $post->post_title;
							$html .= wndt_cert_icon($post->post_author);
							$html .= '</h1>';
							$html .= '<div class="is-hidden-mobile"><span class="icon"><i class="fas fa-map-marker-alt"></i></span>' .
								get_the_term_list($post->ID, 'region', $before = '', $sep = '', $after = ' ') .
								wndt_get_user_company_name($post->post_author) . '</div>';
							$html .= '<div class="tags">' . get_the_term_list($post->ID, $post->post_type . '_tag', '', '', '') . '</div>';
							$html .= '<div>' . wnd_modal_button('联系我', 'wndt_contact_info', ['post_id' => $post->ID], 'is-outlined is-' . wnd_get_config('primary_color')) . '</div>';
							$html .= '</div>';

							echo $html;
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php
}
