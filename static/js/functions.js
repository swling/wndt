jQuery(document).ready(function($) {
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