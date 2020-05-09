var $ = jQuery.noConflict();

function wndt_focus_on_sidebar(close = false) {
	if ($("#sidebar-menu.is-active").length > 0 || close) {
		$("#sidebar-menu.is-active").animate({
			left: '-' + $("#sidebar-menu").width()
		});
		$("body").removeClass("focus-sidebar");
		$(".navbar .navbar-burger").removeClass("is-active");
		$("#sidebar-menu").removeClass("is-active");
		$("#modal-background").removeClass("is-active");
		$("#modal-background").remove();
	} else {
		$("#sidebar-menu").animate({
			left: "0"
		});
		$(".navbar .navbar-burger").addClass("is-active");
		$("#sidebar-menu").addClass("is-active");
		$("#modal-background").addClass("is-active");
		wndt_reset_modal();
	}
}

// 初始化对话框
function wndt_reset_modal() {
	$("#modal").remove();
	$("body").append(
		'<div id="modal-background" class="modal is-active">' +
		'<div class="modal-background"></div>' +
		'</div>'
	);
}

jQuery(document).ready(function($) {
	$("body").on("click", "#modal-background .modal-background", function() {
		wndt_focus_on_sidebar(true);
	})

	/**
	 *@since 2019.07.11 从主题中移植
	 *移动导航点击展开效果
	 */
	$("body").on("click", ".navbar-burger", function() {
		wndt_focus_on_sidebar();
	});

	/**
	 *@since 2019.02.18 点击菜单 新增active
	 */
	$("body").on("click", ".menu a", function() {
		$(this).parents(".menu").find("a").removeClass("is-active");
		$(this).addClass("is-active");

		// 侧边栏菜单
		if ($(this).parents().is("#sidebar-menu")) {
			wndt_focus_on_sidebar(true);
		}
	});

	/**
	 *@since 2019.02.18 点击Tabs 新增active
	 */
	$("body").on("click", ".tabs a", function() {
		$(this).parent("li").addClass("is-active");
		$(this).parent("li").siblings().removeClass("is-active");
	});

	// 表单字数限制
	$("body").on("keyup", "[type='text']", function() {
		if ($(this).val().length > 100) {
			$(this).val($(this).val().substring(0, 100));
		}
	});
	$("body").on("keyup", "[name='_post_post_title']", function() {
		if ($(this).val().length > 60) {
			$(this).val($(this).val().substring(0, 60));
		}
	});
	$("body").on("keyup", "[name='_wpusermeta_description']", function() {
		if ($(this).val().length > 200) {
			$(this).val($(this).val().substring(0, 200));
		}
	});

});