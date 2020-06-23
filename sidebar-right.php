<div id="right-sidebar" class="column is-3 columns">

	<div class="menu">
		<div class="columns">
			<p class="menu-label">
				分类
			</p>
			<ul class="menu-list">
				<?php
				$terms = get_terms($args = array('taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'count'));
				foreach ($terms as $term) {
					echo '<li><a href="' . get_term_link($term) . '">' . $term->name . '</a></li>';
				}

				// $mypages = get_pages(['sort_column' => 'post_date', 'sort_order' => 'desc']);
				// foreach ($mypages as $page) {
				// 	echo '<a class="navbar-item" href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a>';
				// }
				// unset($page);
				?>
			</ul>
		</div>
	</div>

</div>