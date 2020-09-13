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
	$Wndt_Login_Social = Wndt\Utility\Wndt_Login_Social::get_instance($state);
	$Wndt_Login_Social::login();
}

get_header();
echo '<main class="column">';
echo '<div class="main box">';
switch ($action) {
case 'submit':
	echo Wndt\Module\Wndt_Post_Submit::render($post_type);
	break;

case 'edit':
	echo Wndt\Module\Wndt_Post_Edit::render($post_id);
	break;

case 'admin':
	echo Wndt\Module\Wndt_Admin::render($post_id);
	break;

default:
	echo Wndt\Module\Wndt_User_Center::render();
	break;
}
echo '</div>';
echo '</main>';

get_footer();
