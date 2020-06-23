<?php

/**
 *Template Name: 用户中心
 *层级：一级
 *slug:ucenter
 */

$action    = $_GET['action'] ?? false;
$post_type = $_GET['type'] ?? 'supply';
$post_id   = $_GET['post_id'] ?? 0;
$state     = $_GET['state'] ?? false;

//监听社交登录 可能有跳转，因此需要在header之前
if ($state) {
	$Wndt_Login_Social = Wndt\Model\Wndt_Login_Social::get_instance($state);
	$Wndt_Login_Social::login();
}

get_header();
echo '<main class="column">';
echo '<div class="main box">';
if ('submit' == $action) {
	echo Wndt\Module\Wndt_Post_Submit::build($post_type);
} elseif ('edit' == $action) {
	echo Wndt\Module\Wndt_Post_Edit::build($post_id);
} else {
	echo Wndt\Module\Wndt_User_Center::build();
}
echo '</div>';
echo '</main>';
?>
<script type="text/javascript">
	function user_center_hash() {
		var hash = location.hash;
		if (!hash) {
			return;
		}

		var element = hash.replace("#", "")
		$("#user-panel-tabs li").removeClass("is-active");
		$("li." + element).addClass("is-active");
		wnd_ajax_embed("#user-center .ajax-container", element);
	}

	// 用户中心Tabs
	user_center_hash();
	window.onhashchange = user_center_hash;
</script>
<?php
// footer
get_footer();
