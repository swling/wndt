/*
 *#################### 初始化
 */
var $ = jQuery.noConflict();

/**
 *定义API接口，添加语言参数
 *
 */
var lang_query = wnd.lang ? '?lang=' + wnd.lang : '';
var wnd_interface_api = wnd.root_url + wnd.interface_api + lang_query;
var wnd_action_api = wnd.root_url + wnd.action_api + lang_query;
var wnd_posts_api = wnd.root_url + wnd.posts_api + lang_query;
var wnd_users_api = wnd.root_url + wnd.users_api + lang_query;
var wnd_jsonget_api = wnd.root_url + wnd.jsonget_api + lang_query;

/**
 *判断元素是否在可视范围
 *@param element jQuery选择器
 *@param inViewType 可视类型：bottom / top / both
 */
function wnd_in_view(element, inViewType) {
	var viewport = {};
	viewport.top = $(window).scrollTop();
	viewport.bottom = viewport.top + $(window).height();
	var bounds = {};
	bounds.top = $($(element)).offset().top;
	bounds.bottom = bounds.top + $($(element)).outerHeight();
	switch (inViewType) {
		case "bottom":
			return ((bounds.bottom <= viewport.bottom) && (bounds.bottom >= viewport.top));
		case "top":
			return ((bounds.top <= viewport.bottom) && (bounds.top >= viewport.top));
		case "both":
			return ((bounds.top >= viewport.top) && (bounds.bottom <= viewport.bottom));
		default:
			return ((bounds.top >= viewport.top) && (bounds.bottom <= viewport.bottom));
	}
};

// js 获取cookie
function getCookie(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=")
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1
			c_end = document.cookie.indexOf(";", c_start)
			if (c_end == -1) c_end = document.cookie.length
			return unescape(document.cookie.substring(c_start, c_end))
		}
	}
	return false;
}

// js 写入cookie
function setCookie(name, value, seconds, path = "/") {
	seconds = seconds ? (seconds * 1000) : 0; //转化为妙
	if (!seconds) return false;
	var expires = "";
	var date = new Date();
	date.setTime(date.getTime() + seconds);
	expires = "; expires=" + date.toGMTString();
	document.cookie = name + "=" + escape(value) + expires + "; path=" + path; //转码并赋值
}

//判断是否为移动端
function wnd_is_mobile() {
	if (navigator.userAgent.match(/(iPhone|iPad|iPod|Android|ios|App\/)/i)) {
		return true;
	} else {
		return false;
	}
}

/**
 *@since 2019.02.14 搜索引擎爬虫
 */
function wnd_is_spider() {
	var userAgent = navigator.userAgent;
	// 蜘蛛判断
	if (userAgent.match(/(Googlebot|Baiduspider|spider)/i)) {
		return true;
	} else {
		return false;
	}
}

//发送按钮倒计时
var wait = 90; // 获取验证码短信时间间隔 按钮不能恢复 可检查号码
var send_countdown;

function wnd_send_countdown() {
	if (wait > 0) {
		$(".send-code").text(wait);
		wait--;
		send_countdown = setTimeout(wnd_send_countdown, 1000);
	} else {
		$(".send-code").text(wnd.msg.try_again).prop("disabled", false).fadeTo("slow", 1);
		wait = 90;
	}
}

/**
 *@since 2019.1 bulma重构
 */

//弹出bulma对话框
function wnd_alert_modal(html, is_gallery = false) {
	wnd_reset_modal();
	if (is_gallery) {
		$(".modal").addClass("is-active wnd-gallery");
	} else {
		$(".modal").addClass("is-active");
		$(".modal-entry").addClass("box");
	}
	$(".modal-entry").html(html);
}

// 直接弹出消息
var ajax_alert_time_out;

function wnd_alert_msg(msg, wait = 0) {
	wnd_reset_modal();
	$(".modal").addClass("is-active");
	$(".modal-entry").removeClass("box");
	$(".modal-entry").html('<div class="alert-message has-text-white has-text-centered">' + msg + '</div>');
	// 定时关闭
	if (wait > 0) {
		ajax_alert_time_out = setTimeout(function() {
			wnd_reset_modal()
		}, wait * 1000);
	}

}

// 在表单提交时反馈信息
var ajax_msg_time_out;

function wnd_ajax_msg(msg, style = "is-danger", parent = "body", wait = 0) {
	$(parent + " .ajax-message:first").html('<div class="message ' + style + '"><div class="message-body">' + msg + '</div></div>');
	// 定时清空
	if (wait > 0) {
		ajax_msg_time_out = setTimeout(function() {
			$(parent + " .ajax-message:first").empty();
		}, wait * 1000);
	}
}

// 初始化对话框
function wnd_reset_modal() {
	// 清空定时器
	clearTimeout(ajax_msg_time_out);
	clearTimeout(ajax_alert_time_out);
	clearTimeout(send_countdown);

	if ($("#modal").length) {
		$("#modal").removeClass("is-active wnd-gallery");
		$("#modal .modal-entry").removeClass("box");
		$("#modal .modal-entry").empty();
	} else {
		$("body").append(
			'<div id="modal" class="modal">' +
			'<div class="modal-background"></div>' +
			'<div class="modal-content">' +
			'<div class="modal-entry content"></div>' +
			'</div>' +
			'<button class="modal-close is-large" aria-label="close"></button>' +
			'</div>'
		);
	}
}

// 点击触发，点击A 元素 触发 B元素点击事件 用于部分UI优化操作
function wnd_click(target_element) {
	$(target_element).trigger("click");
}

// 表单提交确认对话框  注意：提交表单的触发按钮，不应该是submit or button 而应该单独定义如 input 等，绑定本函数
function wnd_confirm_form_submit(form_id, msg = "") {
	wnd_alert_modal(
		'<p>' + msg + '</p>' +
		'<div class="field is-grouped is-grouped-centered">' +
		'<div class="control">' +
		'<button class="button is-dark"  onclick="wnd_ajax_submit(\'' + form_id + '\')" >' + wnd.msg.confirm + '</button>' +
		'</div>' +
		'</div>'
	);
}

/**
 * @since 2019.1
*######################## ajax动态内容请求
*使用本函数主要用以返回更复杂的结果如：表单，页面以弹窗或嵌入指定DOM的形式呈现，以供用户操作。表单提交则通常返回较为简单的结果
	允许携带一个参数

	@since 2019.01.26
	若需要传递参数值超过一个，可将参数定义为GET参数形式如："post_id=1&user_id=2"，后端采用:wp_parse_args() 解析参数

	实例：
		前端
		wnd_ajax_modal("wnd_xxx","post_id=1&user_id=2");

		后端
		namespace Wnd\Module;
		class wnd_xxx($args){
			$args = wp_parse_args($args)
			return($args);
		}
		弹窗将输出
		Array ( [post_id] => 1 [user_id] =>2)

*典型用途：	击弹出登录框、点击弹出建议发布文章框
*@param 	module 		string 		module类
*@param 	param 		srting 		传参
*/
// ajax 从后端请求内容，并以弹窗形式展现
function wnd_ajax_modal(module, param = '') {
	$.ajax({
		type: "GET",
		url: wnd_interface_api,
		data: {
			"module": module,
			"param": param,
			"ajax_type": "modal",
		},
		//后台返回数据前
		beforeSend: function(xhr) {
			xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
			wnd_alert_msg("……");
		},
		//成功后
		success: function(response) {
			wnd_reset_modal();
			if (typeof response == "object") {
				wnd_alert_msg(response.msg);
			} else {
				wnd_alert_modal(response);
			}
		},
		// 错误
		error: function() {
			wnd_alert_msg(wnd.msg.system_error);
		}
	});
}

/**
 *@since 2019.1.10  从后端请求ajax内容并填充到指定DOM
 *原理同 wnd_ajax_modal()，区别为，响应方式为嵌入
 *@param 	container 	srting 		指定嵌入的容器选择器
 *@param 	module 		string 		module类名称
 *@param 	param 		srting 		传参
 **/
function wnd_ajax_embed(container, module, param = 0) {
	$.ajax({
		type: "GET",
		url: wnd_interface_api,
		data: {
			"module": module,
			"param": param,
			"ajax_type": "embed",
		},
		//后台返回数据前
		beforeSend: function(xhr) {
			xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
			$(container).addClass("is-loading");
		},
		//成功后
		success: function(response) {
			// 清除加载中效果
			$(container).removeClass("is-loading");

			if (typeof response == "object") {
				wnd_alert_msg(response.msg);
			} else {
				$(container).html(response);
			}
		},
		// 错误
		error: function() {
			wnd_alert_msg(wnd.msg.system_error);
		}
	});
}

/**
 * 根据当前表单可视状态，展示操作响应消息
 * @since 2020.04.23
 */
function wnd_ajax_form_msg(form_id, msg, style) {
	// 获取element
	var ajax_msg_element = $("#" + form_id + " .ajax-message:first");

	// 是否在弹窗中操作
	var is_in_modal = $("#" + form_id).closest(".modal.is-active").length ? true : false;

	if (is_in_modal || wnd_in_view(ajax_msg_element, "bottom")) {
		wnd_ajax_msg(msg, style, "#" + form_id);
	} else {
		wnd_alert_msg('<div class="message ' + style + '"><div class="message-body">' + msg + '</div></div>');
	}
}

//############################### 根据表单id自动提交表单 并根据返回代码执行对应操作
function wnd_ajax_submit(form_id) {
	// 提交按钮
	var submit_button = $("#" + form_id + " [type='submit']");


	// 带有required属性的字段不能为空
	var input_value = true;
	$("#" + form_id + " input").each(function() {
		if ($(this).prop("required")) {
			if ($(this).val() == "") {
				input_value = false;
				$(this).addClass("is-danger");
			}
			if ($(this).attr("type") == "radio" || $(this).attr("type") == "checkbox") {
				if ($('[name=' + $(this).attr("name") + ']:checked').length <= 0) {
					input_value = false;
					$(this).addClass("is-danger");
				}
			}
		}
	});

	// 下拉必选，默认未选择 value = -1
	var option_value = true;
	$("#" + form_id + " select option:selected").each(function() {
		// 查看当前下拉option的父元素select是否具有required属性
		option_value = $(this).val();
		if ($(this).parent().prop("required") && option_value == "-1") {
			option_value = false;
			$("#" + form_id + " select").addClass("is-danger");
			return false; //此处为退出each循环，而非阻止提交
		}
	});

	// 文本框
	var textarea_value = true;
	$("#" + form_id + " textarea").each(function() {
		if ($(this).prop("required")) {
			if ($(this).val() == "") {
				textarea_value = false;
				$(this).addClass("is-danger");
			}
		}
	});

	if (input_value === false || option_value === false || textarea_value === false) {
		wnd_ajax_form_msg(form_id, wnd.msg.required, "is-warning");
		return false;
	}

	// ########################### 提取wp_editor编辑器内容
	if ($(".wp-editor-area").length > 0) {
		// 获取编辑器id
		var wp_editor_id = $(".wp-editor-area").attr("id");
		var content;
		var editor = tinyMCE.get(wp_editor_id);
		if (editor) {
			content = editor.getContent();
		} else {
			content = $("#" + wp_editor_id).val();
		}
		$("#" + wp_editor_id).val(content);
	}

	// 生成表单数据
	var form_data = new FormData($("#" + form_id).get(0));

	$.ajax({
		url: wnd_action_api,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		data: form_data,
		type: "POST",

		// 提交中
		beforeSend: function(xhr) {
			xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
			submit_button.addClass("is-loading");
		},

		// 返回结果
		success: function(response) {
			submit_button.removeClass("is-loading");
			if (response.status != 0) {
				submit_button.prop("disabled", true);
			}
			var submit_text = (response.status > 0) ? wnd.msg.submit_successfully : wnd.msg.submit_failed;
			submit_button.text(submit_text);

			var style = response.status > 0 ? "is-success" : "is-danger";

			// 根据后端响应处理
			switch (response.status) {

				// 常规类，展示后端提示信息
				case 1:
					wnd_ajax_form_msg(form_id, response.msg, style);
					break;

					//更新类
				case 2:
					var msg = wnd.msg.submit_successfully + '<a href="' + response.data.url + '" target="_blank">&nbsp;' + wnd.msg.view + '</a>';
					wnd_ajax_form_msg(form_id, msg, style);
					break;

					// 跳转类
				case 3:
					wnd_ajax_form_msg(form_id, wnd.msg.waiting, style);
					$(window.location).prop("href", response.data.redirect_to);
					break;

					// 刷新当前页面
				case 4:
					wnd_reset_modal();
					window.location.reload(true);
					break;

					// 弹出信息并自动消失
				case 5:
					wnd_alert_msg(response.msg, 1);
					break;

					// 下载类
				case 6:
					wnd_ajax_form_msg(form_id, wnd.msg.downloading, style);
					$(window.location).prop("href", response.data.redirect_to);
					break;

					// 默认
				default:
					wnd_ajax_form_msg(form_id, response.msg, style);
					break;
			}
		},

		// 提交错误
		error: function() {
			wnd_ajax_form_msg(form_id, wnd.msg.system_error, "is-danger")
			submit_button.removeClass("is-loading");
			submit_button.text(wnd.msg.system_error);
			submit_button.prop("disabled", true);
		},
	});
}

/**
 * 流量统计
 */
function wnd_ajax_update_views(post_id, interval = 3600) {
	if (wnd_is_spider()) {
		return;
	}

	var timestamp = Date.parse(new Date()) / 1000;
	var wnd_views = localStorage.getItem("wnd_views") ? JSON.parse(localStorage.getItem("wnd_views")) : [];
	var max_length = 10;
	var is_new = true;

	// 数据处理
	for (var i = 0; i < wnd_views.length; i++) {
		if (wnd_views[i].post_id == post_id) {
			// 存在记录中：且时间过期
			if (wnd_views[i].timestamp < timestamp - interval) {
				wnd_views[i].timestamp = timestamp;
				var is_new = true;
			} else {
				var is_new = false;
			}
			break;
		}
	}

	// 新浏览
	if (is_new) {
		var new_view = {
			"post_id": post_id,
			"timestamp": timestamp
		};
		wnd_views.unshift(new_view);
	}

	// 删除超过长度的元素
	if (wnd_views.length > max_length) {
		wnd_views.length = max_length;
	}

	// 更新服务器数据
	if (is_new) {
		$.ajax({
			type: "POST",
			datatype: "json",
			url: wnd_action_api,
			data: {
				"param": post_id,
				"action": "wnd_safe_action",
				"method": "update_views",
				"_ajax_nonce": wnd.safe_action_nonce,
			},
			// 提交中
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
			},
			success: function(response) {
				if (response.status === 1) {
					localStorage.setItem("wnd_views", JSON.stringify(wnd_views));
				}
			}
		});
	}
}

//####################### 文档加载完成后才能执行的功能
jQuery(document).ready(function($) {

	/**
	 *@since 2019.1.15 ajax 文件上传
	 */
	$("body").on("change", "[type='file']", function() {

		/**
		 *监听文件字段是否设置了ajax上传属性
		 */
		if ($(this).data("method") != 'ajax') {
			return;
		}

		// 获取当前上传容器ID
		var id = $(this).data("id");

		//创建表单
		var form_data = new FormData();

		// 获取本地上传文件的名称，并去除拓展
		var filename = $(this).val().split('\\').pop();
		filename = filename.substring(0, filename.lastIndexOf('.'));

		// 获取文件，支持多文件上传
		for (var i = 0; i < $(this).get(0).files.length; ++i) {
			form_data.set("wnd_file[" + i + "]", $(this).prop("files")[i]);
		}

		// 获取属性
		for (key in $(this).data()) {
			form_data.set(key, $(this).data(key));
		}

		// 需要前端判断处理的变量
		var meta_key = $(this).data("meta_key");
		var is_image = $(this).data("is_image");
		var thumbnail_width = $(this).data("thumbnail_width");
		var thumbnail_height = $(this).data("thumbnail_height");

		// ajax api请求函数及其nonce
		form_data.set("_ajax_nonce", $(this).data("upload_nonce"));
		form_data.set("action", "wnd_upload_file");

		// ajax中无法直接使用jQuery $(this)，需要提前定义
		var _this = $(this);
		$.ajax({
			url: wnd_action_api,
			dataType: "json",
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: "POST",
			//后台返回数据前
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
				wnd_ajax_msg(wnd.msg.waiting, "is-warning", "#" + id);
			},
			// 提交成功
			success: function(response) {

				// 清空input，防止随表单重复提交文件
				_this.val('');

				// 错误
				if (response.status === 0) {
					wnd_ajax_msg(response.msg, "is-danger", "#" + id);
					return false;
				}

				// 上传文件可能是多个，因此返回值为二维数组
				for (var i = 0, n = response.length; i < n; i++) {
					// 上传失败
					if (response[i].status === 0) {
						wnd_ajax_msg(response[i].msg, "is-danger", "#" + id);
						continue;
					}

					// 上传成功
					wnd_ajax_msg(wnd.msg.upload_successfully, "is-success", "#" + id);

					// 相册
					if (meta_key == "gallery") {
						// 清除默认提示信息
						$("#" + id + " .gallery .default-message").remove();
						var new_img_element =
							'<div class="column is-narrow attachment-' + response[i].data.id + '">' +
							'<a><img class="thumbnail" src="' + response[i].data.thumbnail + '" data-url="' + response[i].data.url + '" width="' + thumbnail_width + '" width="' + thumbnail_height + '"></a>' +
							'<a class="delete" data-id="' + id + '" data-file_id="' + response[i].data.id + '"></a>' +
							'</div>';
						$("#" + id + " .gallery").prepend(new_img_element);

						// 单张图像上传，更新缩略图
					} else if (is_image == 1) {

						//常规单张图片
						$("#" + id + " .thumbnail").prop("src", response[i].data.thumbnail);
						$("#" + id + " .delete").data("file_id", response[i].data.id)

						// 单个文件上传
					} else {
						$("#" + id + " .file-name").html(wnd.msg.upload_successfully + '&nbsp<a href="' + response[i].data.url + '" target="_blank">' + wnd.msg.view + '</a>');
						$("#" + id + " .delete").data("file_id", response[i].data.id)
					}

					/**
					 *@since 2019.07.16
					 *若当前表单为文章表单，且类型为：attachment，则认为当前表单作为为编辑附件详情信息
					 *将当前上传的文件id新增到所属表单字段发送给后端，以供二次处理
					 *
					 *ajax上传时，后端会自动根据post_parent和meta_key做常规处理，此处额外发送，供开发中其他特殊情况使用
					 */
					var parent_form = _this.closest("form");
					if (parent_form.length > 0 && parent_form.find("input[name='_post_post_type']").val() == "attachment") {
						parent_form.find("input[name='_post_ID']").val(response[i].data.id);

						/**
						 *如果当前未手动设定：标题、别名、menu order，则返回自动生成的值
						 *由于插件对上传文件做了自动文件名加密处理，此处自动设置为本地文件名
						 */
						if (!parent_form.find("input[name='_post_post_title']").val()) {
							parent_form.find("input[name='_post_post_title']").val(filename);
						}
						if (!parent_form.find("input[name='_post_post_name']").val()) {
							parent_form.find("input[name='_post_post_name']").val(response[i].data.post.post_name);
						}

						if (!parent_form.find("input[name='_post_menu_order']").val()) {
							parent_form.find("input[name='_post_menu_order']").val(response[i].data.post.menu_order);
						}

						/**
						 *上传后，若再次选择文件，则将自动生成新的attachment记录，当前表单信息也将更改为最新上传的附件
						 *因此上传后因禁用input，避免误操作，如需更换文件，应该先删除当前文件
						 */
						_this.prop("disabled", true);
						wnd_ajax_msg(wnd.msg.upload_successfully, "is-success", "#" + id);

						// 上传后，自动提交保存媒体信息
						wnd_ajax_submit(parent_form.attr("id"));
					}
				}
			},

			// 错误
			error: function() {
				wnd_ajax_msg(wnd.msg.system_error, "is-danger", "#" + id);
			}
		});
	});

	/**
	 *@since 2019.1.15 ajax 文件删除
	 */
	$("body").on("click", ".upload-field .delete", function() {
		if (!confirm(wnd.msg.confirm)) {
			return false;
		}

		// 获取当前上传容器ID
		var id = $(this).data("id");

		// 获取被删除的附件ID
		var file_id = $(this).data("file_id");
		if (!file_id) {
			wnd_ajax_msg(wnd.msg.system_error, "is-danger", "#" + id);
			return false;
		}

		//创建表单
		var form_data = new FormData();

		// 获取属性
		form_data.set("file_id", file_id);
		file_data = $("#" + id + " [type='file']").data();
		for (key in file_data) {
			form_data.set(key, file_data[key]);
		}

		// 需要前端判断处理的变量
		var meta_key = file_data["meta_key"];
		var is_image = file_data["is_image"];
		// 默认图
		var thumbnail = file_data["thumbnail"];

		// ajax api请求函数及其nonce
		form_data.set("_ajax_nonce", file_data["delete_nonce"]);
		form_data.set("action", "wnd_delete_file");

		$.ajax({
			url: wnd_action_api,
			dataType: "json",
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: "post",
			//后台返回数据前
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
				wnd_ajax_msg(wnd.msg.waiting, "is-warning", "#" + id);
			},
			// 上传成功
			success: function(response) {
				if (response.status === 0) {
					wnd_ajax_msg(response.msg, "is-danger", "#" + id);
					return false;
				}
				wnd_ajax_msg(wnd.msg.deleted, "is-success", "#" + id);

				/**
				 *@since 2019.07.18
				 *如果上传后，设置了禁用，当删除文件后恢复文件上传
				 **/
				$("#" + id + " [type='file']").prop("disabled", false);

				/**
				 *@since 2019.07.16
				 * 删除以"attachment-{file_id} 为class的html
				 */
				$(".attachment-" + file_id).remove();

				// 相册
				if (meta_key == "gallery") {
					// 删除全部图片时，显示提示信息
					if ($("#" + id + " .gallery").html() == "") {
						$("#" + id + " .gallery").html('<div class="column default-message"><p>' + wnd.msg.deleted + '</p></div>')
					}

					// 单张图片
				} else if (is_image == 1) {
					$("#" + id + " .thumbnail").prop("src", thumbnail);
					// 清空删除按钮数据绑定
					$("#" + id + " .delete").data("file_id", 0)

					// 单个文件
				} else {
					$("#" + id + " .file-name").text(wnd.msg.deleted);
					// 清空删除按钮数据绑定
					$("#" + id + " .delete").data("file_id", 0)
				}
			},
			// 错误
			error: function() {
				wnd_ajax_msg(wnd.msg.system_error, "is-danger", "#" + id);
			}
		});
	});

	/**
	 *@since 2019.02.09 发送手机或邮箱验证码
	 */
	$("body").on("click", ".send-code", function() {
		// 清除定时器
		clearTimeout(send_countdown);
		wait = 90;

		// ajax中无法直接使用jQuery $(this)，需要提前定义
		var _this = $(this);
		var form_id = _this.closest("form").attr("id");

		var data = $(this).data();
		data.action = "wnd_send_code";
		data.phone = $("#" + form_id + " input[name='phone']").val();
		data.email = $("#" + form_id + " input[name='_user_user_email']").val();
		if (data.email == "" || data.phone == "") {
			wnd_ajax_msg(wnd.msg.required, "is-warning", "#" + form_id);
			return false;
		}

		$.ajax({
			type: "post",
			dataType: "json",
			url: wnd_action_api,
			data: data,
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
				_this.addClass("is-loading");
			},
			success: function(response) {
				if (response.status <= 0) {
					var style = "is-danger";
				} else {
					var style = "is-success";
					_this.prop("disabled", true);
					_this.text(wnd.msg.send_successfully);
					wnd_send_countdown();
				}
				wnd_ajax_msg(response.msg, style, "#" + form_id);
				_this.removeClass("is-loading");
			},
			// 错误
			error: function() {
				wnd_ajax_msg(wnd.msg.send_failed, "is-danger", "#" + form_id);
				_this.removeClass("is-loading");
			}
		});
	});

	/**
	 *@since 2019.07.02 非表单形式发送ajax请求
	 */
	var can_click_ajax_link = true;
	$("body").on("click", ".ajax-link", function() {

		// 是否在弹窗中操作
		var is_in_modal = $(this).closest(".modal.is-active").length ? true : false;

		// 点击频率控制
		if (!can_click_ajax_link) {
			return;
		}
		can_click_ajax_link = false;
		setTimeout(function() {
			can_click_ajax_link = true;
		}, 1000);

		if (1 == $(this).data("disabled")) {
			wnd_alert_msg(wnd.msg.waiting, 1);
			return;
		}

		// 判断当前操作是否为取消
		var is_cancel = $(this).data("is-cancel");
		var action = is_cancel ? $(this).data("cancel") : $(this).data("action");
		var nonce = is_cancel ? $(this).data("cancel-nonce") : $(this).data("action-nonce");

		// $(this) 在ajax 中无效，故此需要单独定义变量
		var _this = $(this);
		$.ajax({
			type: "POST",
			url: wnd_action_api,
			data: {
				"action": action,
				"param": $(this).data("param"),
				"_ajax_nonce": nonce,
			},
			//后台返回数据前
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
			},
			//成功后
			success: function(response) {

				// 正向操作成功
				if (response.status != 0 && action == _this.data("action")) {
					// 设置了逆向操作
					if (_this.data("cancel")) {
						_this.data("is-cancel", 1);
						// 未设置逆向操作，禁止重复点击事件
					} else {
						_this.data("disabled", 1);
					}

				} else {
					_this.data("is-cancel", 0);
				}

				// 根据后端响应处理
				switch (response.status) {
					// 常规类，展示后端提示信息
					case 1:
						_this.html(response.data);
						break;

						// 弹出消息
					case 2:
						if (response.data) {
							_this.html(response.data);
						}
						if (!is_in_modal) {
							wnd_alert_msg(response.msg, 1);
						}
						break;

						// 跳转类
					case 3:
						wnd_alert_msg(wnd.msg.waiting);
						$(window.location).prop("href", response.data.redirect_to);
						break;

						// 刷新当前页面
					case 4:
						wnd_reset_modal();
						window.location.reload(true);
						break;

						//默认展示提示信息
					default:
						_this.html(response.data);
						if (!is_in_modal) {
							wnd_alert_msg(response.msg, 1);
						}
						break;
				}
			},
			// 错误
			error: function() {
				wnd_alert_msg(wnd.msg.system_error);
			}
		});
	});

	/**
	 *@since 2019.07.31 Wnd_Filter筛选
	 *点击链接，获取对应data值，组合参数并发送ajax请求
	 */
	var filter_param = {};
	$("body").on("click", ".ajax-filter a,.ajax-filter form button", function() {

		// 获取容器
		var filter_parent = $(this).closest(".ajax-filter");
		var filter_id = filter_parent.attr("ID").split("-")[1];

		// 定义 key、value
		var key = "";
		var value = "";

		// 提取data，并合并入参数
		var html_data = filter_parent.data();
		filter_param = Object.assign(filter_param, html_data);

		// 非分页情况，删除page请求参数
		var is_pagination = $(this).closest(".pagination-list").length > 0 ? true : false;
		if (!is_pagination) {
			delete filter_param.page;
		}

		if ("BUTTON" == $(this).get(0).tagName) {
			var search_form = $(this).closest("form");
			var form_data = search_form.serializeArray();
			$.each(form_data, function() {
				filter_param[this.name] = this.value;
			});
		} else {
			key = $(this).data("key");
			value = $(this).data("value");
			filter_param[key] = value;
		}

		// 值为空删除
		if (!value && typeof filter_param[key] != "undefined") {
			delete filter_param[key];
		}

		// type 切换需要特殊处理，切换时，应该清空所有其余参数
		if ("type" == key) {

			/**
			 *filter_param = html_data;
			 *JavaScript 中，如果直接对数组、对象赋值，会导致关联的双方都同步改变
			 *因此需要做JSON.parse(JSON.stringify(html_data)) 处理
			 */
			filter_param = JSON.parse(JSON.stringify(html_data));
			filter_param['type'] = $(this).data("value");

			/**
			 *清空参数的同时同步清空：is-active
			 **/
			filter_parent.find("ul.tab li").removeClass("is-active");
			filter_parent.find("ul.tab li:first-child").addClass("is-active");

		} else {
			filter_param['type'] = $("#tabs-" + filter_id).find(".post-type-tabs .is-active a").data("value");
		}

		// 主分类切换时删除标签查询
		if ("_term_category" == key) {
			var tag_taxonomy_key = "_term_post_tag";
			delete filter_param[tag_taxonomy_key];
		} else if (key.indexOf("_cat") > 0) {
			var tag_taxonomy_key = key.replace("_cat", "_tag");
			delete filter_param[tag_taxonomy_key];
		}

		var _this = $(this);
		$.ajax({
			url: wnd_posts_api,
			type: "GET",
			data: filter_param,
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
				$(filter_param.wnd_ajax_container).addClass("is-loading");
			},
			success: function(response) {
				$(filter_param.wnd_ajax_container).removeClass("is-loading");

				// 切换post type时，隐藏所有taxonomy 再根据当前post type支持的taxonomy选择性显示，以达到ajax切换的效果
				if ("type" == key) {
					filter_parent.find(".taxonomy-tabs").addClass("is-hidden");
					for (var i = 0; i < response.data.taxonomies.length; i++) {
						filter_parent.find("." + response.data.taxonomies[i] + "-tabs").removeClass("is-hidden");
					}
				}

				// 嵌入查询结果
				$(filter_param.wnd_ajax_container).html(response.data.posts + response.data.pagination);

				/**
				 *@since 2019.08.10
				 *主分类：当前tabs设置了主分类筛选项，且切换type后当前type主分类不存在时，插入当前type主分类
				 *动态插入主分类的情况，通常用在用于一些封装的用户面板：如果用户内容管理面板
				 *常规filter应该通过 Wnd_Filter->add_taxonomy_filter() 方法静态输出
				 */
				var category_tabs = $(filter_parent).children(".main-category-tabs");
				if (
					category_tabs &&
					response.data.category_tabs &&
					$(filter_parent).find("." + response.data.category_taxonomy + "-tabs").length == 0
				) {
					category_tabs.last().after(response.data.category_tabs);
					category_tabs.remove();
				}

				// 分类关联标签tabs
				var related_tags_tabs = $(filter_parent).children(".related-tags");
				if (related_tags_tabs && response.data.related_tags_tabs) {
					related_tags_tabs.after(response.data.related_tags_tabs);
					related_tags_tabs.remove();
				} else {
					related_tags_tabs.addClass("is-hidden");
				}

				// 子类筛选tabs
				$(filter_parent).children(".sub-tabs").remove();
				for (taxonomy in response.data.sub_taxonomy_tabs) {
					$(filter_parent).children("." + taxonomy + "-tabs:last").after(response.data.sub_taxonomy_tabs[taxonomy]);
				}
			},
			error: function() {
				wnd_alert_msg(wnd.msg.system_error);
				_this.parent("li").removeClass("is-active");
			}
		});

		// 阻止链接跳转
		return false;
	});

	/**
	 *@since 2020.05.06 Wnd_User_Filter筛选
	 *点击链接，获取对应data值，组合参数并发送ajax请求
	 */
	var filter_user_param = {};
	$("body").on("click", ".ajax-filter-user a,.ajax-filter-user form button", function() {
		// 获取容器
		var filter_parent = $(this).closest(".ajax-filter-user");
		var filter_id = filter_parent.attr("ID").split("-")[1];

		// 定义 key、value
		var key = "";
		var value = "";

		// 提取data，并合并入参数
		var html_data = filter_parent.data();
		filter_user_param = Object.assign(filter_user_param, html_data);

		// 非分页情况，删除page请求参数
		var is_pagination = $(this).closest(".pagination-list").length > 0 ? true : false;
		if (!is_pagination) {
			delete filter_user_param.page;
		}

		if ("BUTTON" == $(this).get(0).tagName) {
			var search_form = $(this).closest("form");
			var form_data = search_form.serializeArray();
			$.each(form_data, function() {
				filter_user_param[this.name] = this.value;
			});
		} else {
			key = $(this).data("key");
			value = $(this).data("value");
			filter_user_param[key] = value;
		}

		// 值为空删除
		if (!value && typeof filter_user_param[key] != "undefined") {
			delete filter_user_param[key];
		}

		var _this = $(this);
		$.ajax({
			url: wnd_users_api,
			type: "GET",
			data: filter_user_param,
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
				$(filter_user_param.wnd_ajax_container).addClass("is-loading");
			},
			success: function(response) {
				$(filter_user_param.wnd_ajax_container).removeClass("is-loading");

				// 嵌入查询结果
				$(filter_user_param.wnd_ajax_container).html(response.data.users + response.data.pagination);
			},
			error: function() {
				wnd_alert_msg(wnd.msg.system_error);
				_this.parent("li").removeClass("is-active");
			}
		});

		// 阻止链接跳转
		return false;
	});

	/**
	 *@since 2020.04.14
	 *分类ajax分级联动下拉
	 */
	$("body").on("change", "select.dynamic-sub", function() {
		var taxonomy = $(this).prop("name");
		taxonomy = taxonomy.split("_term_")[1];
		taxonomy = taxonomy.split("[]")[0];

		var term_id = $(this).val();
		var child_level = $(this).data("child_level");
		var required = $(this).prop("required");

		var child_selector = $("." + taxonomy + "-child-" + (child_level + 1));
		var tips = child_selector.data("tips") || "";

		// 一级分类变动，则重置所有子类
		if (0 == child_level) {
			$(".dynamic-sub-" + taxonomy).each(function() {
				$(this).html('<option value="-1">- ' + $(this).data("tips") + ' -</option>');
				$(this).removeClass("is-active");
			});;

			$(".dynamic-sub-" + taxonomy).prop("disabled", false);
		}

		if (term_id != -1) {
			$.ajax({
				type: "get",
				url: wnd_jsonget_api,
				data: {
					"data": "wnd_sub_terms",
					"param": {
						"parent": term_id,
						"taxonomy": taxonomy,
						"tips": tips,
					},
				},

				success: function(html) {
					/**
					 *如果子类可选，则添加父类required属性; 反之，无论父类是否必选，子类必须移除required
					 *如果子级分类可用，则子级及往后其他子类移除可能存在的disabled属性
					 *如果子级分类不可用，则子级及往后其他子类设置为disabled
					 */
					if (html) {
						child_selector.html(html);
						child_selector.prop("required", required);
						child_selector.addClass("is-active");
						$(".dynamic-sub-" + taxonomy).each(function() {
							if ($(this).data("child_level") > child_level) {
								$(this).prop("disabled", false);
							}
						});
					} else {
						child_selector.prop("required", false);
						$(".dynamic-sub-" + taxonomy).each(function() {
							if ($(this).data("child_level") > child_level) {
								$(this).prop("disabled", true);
							}
						});
					}
				}
			});
		} else {
			child_selector.html('<option value="-1">- ' + child_selector.data("tips") + ' -</option>');
		}
	});

	/**
	 *@since 2020.04.20
	 *动态追加字段
	 */
	// add row
	$("body").on("click", "form .add-row", function() {
		var parent = $(this).closest(".field");

		// 临时改变属性，以便复制给即将创建的字段
		$(this).removeClass("add-row");
		$(this).addClass("remove-row");
		$(this).text("-");

		// 获取改变后的Html
		var html = parent[0].outerHTML;

		// 还原当前字段
		$(this).addClass("add-row");
		$(this).removeClass("remove-row");
		$(this).text("+");

		// 创建新字段
		parent.after(html);
	});
	// remov row
	$("body").on("click", "form .remove-row", function() {
		$(this).closest(".field").remove();
	});

	/**
	 *@since 2019.03.28 表单改变时，移除提交按钮禁止状态,恢复提交文字
	 */
	$("body").on("change", "form", function() {
		var submit_button = $(this).find("[type='submit']");
		submit_button.prop("disabled", false);
		submit_button.text(submit_button.data("text"));
	});

	/**
	 *@since 2019.03.10 ajax提交表单
	 */
	$("body").on("click", "[type='submit'].ajax-submit", function() {
		var form_id = $(this).closest("form").attr("id");
		if (form_id) {
			wnd_ajax_submit(form_id);
		} else {
			wnd_alert_msg(wnd.msg.system_error, 1);
		}
	});

	/**
	 *@since 2019.05.07 相册放大
	 */
	$("body").on("click", ".gallery img", function() {
		var images = $(this).closest(".gallery").find("img");

		/**
		 *@link http://www.w3school.com.cn/jquery/traversing_each.asp
		 */
		var element = '<img src="' + $(this).data("url") + '" />';
		wnd_alert_modal(element, true);
	});

	/**
	 *@since 2020.03.28
	 *关闭弹窗
	 */
	$("body").on("click", "#modal .modal-background,#modal .modal-close", function() {
		$(this).closest("#modal").remove();
	});

	/**
	 *@since 2020.04.23
	 *关闭通知
	 */
	$("body").on("click", ".notification .delete", function() {
		$(this).closest(".notification").slideUp();
	});
});