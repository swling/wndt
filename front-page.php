<?php

use Wnd\Template\Wnd_Term_Tpl;

$primary_post_type = 'post';
get_header();
?>
<div id="main" class="column columns content is-multiline is-paddingless">
	<div class="column">
		<?php
		if (0 and wnd_get_sticky_posts($primary_post_type)) {
			echo '<div class="is-hidden-mobile has-background-white box">';
			echo '<div class="container">';
			echo '<div class="columns is-multiline has-text-centered">';
			$company_posts = wnd_get_sticky_posts($primary_post_type, 6);
			foreach ($company_posts as $company_post_id) {
				echo '<div class="column is-2">' . wndt_company_profile($company_post_id, 120, false) . '</div>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} ?>

		<div class="box has-background-white">
			<div class="container">
				<div class="tabs is-small">
					<ul class="is-marginless">
						<li class="is-active">
							<a href="<?php echo home_url($primary_post_type); ?>">
								<?php echo get_post_type_object($primary_post_type)->label; ?>
							</a>
						</li>
					</ul>
				</div>
				<?php echo Wnd_Term_Tpl::list_categories_with_tags('category', $primary_post_type . '_tag', 6); ?>
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
