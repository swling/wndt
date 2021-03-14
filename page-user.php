<?php
/**
 *Template Name: 前端页面
 *
 */

use Wnd\Module\Wnd_Front_Page;

/**
 * 获取 URL 参数并按格式组合传递给 Wnd_User_Page
 * 事实上，Wnd Frontend Module 相关模块默认会自动获取 $_GET 参数。如果 $_GET 参数格式完整契合如下参数，可不用传递参数
 * 此处多此一举，意在帮助主题开发者快速理解用户中心和 $_GET 参数的关系
 * （WP 环境中 $_GET 参数无法直接传递 ['post_type'] 统一为 ['type']）
 *
 * 页面功能：
 * - 根据 URL 参数 $_GET['state'] 处理社交登录（绝大部分社交登录均支持在回调 URL 中添加 $_GET['state']，如有例外后续补充处理）
 * - 根据 URL 参数 $_GET['module'] 呈现对应 UI 模块
 * - 根据 URL 参数 $_GET['action'] = （submit/edit） 调用对应内容发布/编辑表单模块
 * - 默认为用户中心：注册、登录、账户管理，内容管理，财务管理等
 */
$args = [
	'state'     => $_GET['state'] ?? '',
	'module'    => $_GET['module'] ?? '',
	'action'    => $_GET['action'] ?? '',
	'post_type' => $_GET['type'] ?? '',
	'post_id'   => $_GET['post_id'] ?? 0,
];

echo Wnd_Front_Page::render($args);
