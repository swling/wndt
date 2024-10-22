</div>
<!--columns-->
</div>
<!--wrap-->
<footer id="footer" class="footer">
	<div class="container">
		<?php if (is_home()) { ?>
			<div class="columns">
				<div class="column is-narrow">
					<div class="logo is-size-5">
						<?php echo stripslashes(wndt_get_config('logo')); ?>
						<a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a>
					</div>
				</div>
				<div class="column">
					<?php echo wndt_list_bookmarks(); ?>
				</div>
				<div class="column content">
					<h3>文档·资讯</h3>
					<ul>
						<?php
						foreach (get_terms(['taxonomy' => 'category', 'parent' => 0, 'orderby' => 'count']) as $category) {
							$class = is_category($category->term_id) ? ' class="is-active"' : '';
							echo '<li' . $class . '><a href="' . get_term_link($category, 'category') . '">' . $category->name . '</a></li>';
						}
						?>
					</ul>
				</div>
				<div class="column is-narrow content">
					<h3>联系方式</h3>
					<p>邮件：245484493@qq.com</p>
					<p>地址：重庆三块石科技有限公司</p>
				</div>
			</div>
		<?php } ?>

		<p id="beian" class=" has-text-centered is-size-7">
			<?php
			$icp 	= wndt_get_config('icp');
			$wangan = wndt_get_config('wangan');
			if ($icp) {
				echo '<a target="_blank" rel="nofollow" href="http://beian.miit.gov.cn">' . $icp . '</a>&nbsp;';
			}
			if ($wangan) {
				echo '<a target="_blank" rel="nofollow" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=' . $wangan . '">';
				echo '<img src="' . WNDT_URL . '/static/images/ghs.png" alt="公安备案">网安备' . $wangan;
				echo '</a>';
			}
			?>
		</p>
		<p id="copyright" class="has-text-centered is-size-7">
			©2013 - <?php echo date('Y'); ?>
			<?php bloginfo('name') ?> Copyright
		</p>
		<p class="has-text-centered">
			<?php echo 'Files:' . count(get_included_files()); ?> -
			<?php echo 'Queries:' . get_num_queries(); ?> - <?php echo timer_stop(); ?> -
			<?php echo number_format(memory_get_peak_usage() / 1024 / 1024, 4); ?>
		</p>
	</div>
</footer>
<?php wp_footer(); ?>
<div class="is-hidden"><?php echo stripslashes(wndt_get_config('statistical_code')); ?></div>
</body>

</html>