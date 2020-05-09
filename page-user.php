<?php

/**
 *Template Name: 用户中心
 *层级：一级
 *slug:ucenter
 */

//监听社交登录 可能有跳转，因此需要在header之前
$state = $_GET['state'] ?? false;
if ($state) {
	$Wndt_Login_Social = Wndt\Model\Wndt_Login_Social::get_instance($state);
	$Wndt_Login_Social::login();
}

get_header();

echo Wndt\Module\Wndt_User_Center::build();

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
		$("#user-center").removeClass("is-active");

		wnd_ajax_embed("#user-main .ajax-container", element);
	}

	// 用户中心Tabs
	user_center_hash();
	window.onhashchange = user_center_hash;
</script>
<?php
// footer
get_footer();
