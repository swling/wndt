<div id="right-sidebar" class="sidebar column is-3">
	<div class="menu">
		<p class="menu-label">
			导航栏
		</p>
		<ul class="menu-list">
			<?php
			$mypages = get_pages(['sort_column' => 'post_date', 'sort_order' => 'desc']);
			foreach ($mypages as $page) {
				echo '<a class="navbar-item" href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a>';
			}
			unset($page);
			?>
		</ul>
		<p class="menu-label">
			帮助中心
		</p>
		<ul class="menu-list">
			<li><a>Dashboard</a></li>
			<li><a>Customers</a></li>
		</ul>
	</div>
</div>