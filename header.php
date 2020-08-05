<!Doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">

<head>
	<link rel="icon" href="<?php echo WNDT_URL; ?>/favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="referrer" content="always" />
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title>
		<?php echo wp_get_document_title(); ?>
	</title>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="top">
		<div class="container">
			<nav class="navbar is-transparent">
				<div class="navbar-brand">
					<div id="site-logo" class="navbar-item is-size-2 is-size-3-mobile">
						<?php echo stripslashes(wndt_get_config('logo')); ?>
					</div>
					<div id="site-title" class="navbar-item">
						<a href="<?php echo home_url(); ?>"><span class="is-size-6-mobile is-size-5"><?php bloginfo('name'); ?></span></a>
						<span class="is-size-6 is-size-6-mobile"><?php print_r(wndt_get_sub_title('&nbsp;|&nbsp;')); ?></span>
					</div>
					<div class="navbar-burger">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</div>
				<div class="navbar-menu">
					<div class="navbar-start">
						<?php echo wndt_menu('primary_menu'); ?>
					</div>
					<div class="navbar-end">
						<a class="navbar-item" onclick="wnd_ajax_modal('wndt_search_form')">
							<span class="icon"><i class="fas fa-search"></i></span>&nbsp;搜索
						</a>
						<a class="navbar-item" href="<?php echo home_url('ucenter/?action=submit&type=post'); ?>">
							<span class="icon"><i class="fas fa-plus"></i></span>&nbsp;发布
						</a>
						<?php if (!is_user_logged_in()) { ?>
							<a class="navbar-item" onclick="wnd_ajax_modal('wnd_user_center','do=login')"><span class="icon"><i class="fa fa-user"></i></span> 登录 / 注册</a>
						<?php } else { ?>
							<div class="navbar-item has-dropdown is-hoverable">
								<a class="navbar-link" href="<?php echo home_url('ucenter') ?>">
									<span class="icon is-small"><i class="fa fa-cog"></i></span>
									<span <?php echo wnd_get_mail_count() ? 'data-badge="' . wnd_get_mail_count() . '"' : 0; ?>>&nbsp;管理</span>
								</a>
								<div class="navbar-dropdown is-right">
									<a class="navbar-item" href="<?php echo home_url('ucenter'); ?>">用户中心</a>
									<a class="navbar-item" href="<?php echo home_url('ucenter?action=admin'); ?>">控制面板</a>
									<?php if (is_super_admin()) { ?>
										<a class="navbar-item" href="<?php echo home_url('ucenter/?action=submit&type=post'); ?>">发布文章</a>
									<?php } ?>
									<a class="navbar-item" href="<?php echo wp_logout_url(home_url()); ?>">退出账户</a>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</nav>
		</div>
	</div>
	<?php include TEMPLATEPATH . '/template-parts/banner/banner.php'; ?>
	<div id="wrap" class="container">
		<?php echo wnd_breadcrumb(); ?>
		<div class="columns is-desktop is-marginless">