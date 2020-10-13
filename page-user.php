<?php
use Wnd\Utility\Wnd_Login_Social;

/**
 *Template Name: 用户中心
 *
 * 页面功能：
 * - 根据 URL 参数 $_GET['state'] 处理社交登录（绝大部分社交登录均支持在回调 URL 中添加 $_GET['state']，如有例外后续补充处理）
 * - 根据 URL 参数 $_GET['module'] 呈现对应 UI 模块
 * - 根据 URL 参数 $_GET['action'] = （submit/edit） 调用对应内容发布/编辑表单模块
 * - 默认为用户中心：注册、登录、账户管理，内容管理，财务管理等
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

// 根据 URL 参数 $_GET['module'] 呈现对应 UI 模块
if ($module) {
	$class = Wnd\Controller\Wnd_API::parse_class($module, 'Module');
	echo $class::render();
} else {
	// 根据 URL 参数 $_GET['action'] = （submit/edit） 调用对应内容发布/编辑表单模块
	switch ($action) {
	case 'submit':
		echo Wndt\Module\Wndt_Post_Submit::render();
		break;

	case 'edit':
		echo Wndt\Module\Wndt_Post_Edit::render();
		break;

	// 默认用户中心：注册、登录、账户管理，内容管理，财务管理等
	default:
		echo Wndt\Module\Wndt_User_Center::render();
		break;
	}
}

echo '</div>';
echo '</main>';

get_footer();
