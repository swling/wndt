<?php
use Wnd\Utility\Wnd_Login_Social;

/**
 *Template Name: 用户中心
 *层级：一级
 *slug:ucenter
 */

$module = $_GET['module'] ?? false;
$action = $_GET['action'] ?? false;
$state  = $_GET['state'] ?? false;

//监听社交登录 可能有跳转，因此需要在header之前
if ($state) {
	$domain       = Wnd_Login_Social::parse_state($state)['domain'];
	$Login_Social = Wnd_Login_Social::get_instance($domain);
	$Login_Social->login();
}

get_header();
echo '<main class="column">';
echo '<div class="main box">';

if ($module) {
	$class = Wnd\Controller\Wnd_API::parse_class($module, 'Module');
	echo $class::render();
} else {
	switch ($action) {
	case 'submit':
		echo Wndt\Module\Wndt_Post_Submit::render();
		break;

	case 'edit':
		echo Wndt\Module\Wndt_Post_Edit::render();
		break;

	default:
		echo Wndt\Module\Wndt_User_Center::render();
		break;
	}
}

echo '</div>';
echo '</main>';

get_footer();
