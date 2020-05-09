/**
 *敬请留意：
 *下列某些选择器可能随wordpress wp_list_comments() 及 comment_form() 变化而失效
 */
$(document).ready(function(jQuery) {
	var __cancel = $("#cancel-comment-reply-link"),
		__cancel_text = __cancel.text(),
		submit_button = $("#commentform [type='submit']");

	$("#commentform").on("submit", function(e) {
		e.preventDefault();
		var form_data = new FormData($("#commentform").get(0));
		jQuery.ajax({
			url: ajaxcomment.api_url,
			data: form_data,
			type: $(this).attr("method"),
			contentType: false,
			processData: false,
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-WP-Nonce", ajaxcomment.rest_nonce);
				addComment.createButterbar("提交中....");

				// 启用WP回复嵌套后，如果使用Ctrl + Enter提交，会提交两次，未找到原因，暂通过提交时禁止submit阻止第二次提交
				submit_button.prop("disabled", true);
			},
			error: function(request) {
				var t = addComment;
				t.createButterbar(request.responseText);

				// 提交时为防止重复提交，禁用了提交按钮，完成后需要恢复
				submit_button.prop("disabled", false);
			},
			success: function(response) {
				if (0 == response.status) {
					var t = addComment;
					t.createButterbar(response.msg);
					return;
				}

				$("textarea").each(function() {
					this.value = ""
				});
				var t = addComment,
					cancel = t.I("cancel-comment-reply-link"),
					temp = t.I("wp-temp-form-div"),
					respond = t.I(t.respondId),
					post = t.I("comment_post_ID").value,
					parent = t.I("comment_parent").value;
				if (parent != "0") {
					$("#respond").before("<ol class=\"children\">" + response.data + "</ol>");
				} else if (!$("." + ajaxcomment.wrapper).length) {
					if (ajaxcomment.formpostion == "bottom") {
						$("#respond").before("<ol class=\"" + ajaxcomment.wrapper + "\">" + response.data + "</ol>");
					} else {
						$("#respond").after("<ol class=\"" + ajaxcomment.wrapper + "\">" + response.data + "</ol>");
					}

				} else {
					if (ajaxcomment.order == "asc") {
						$("." + ajaxcomment.wrapper).append(response.data); // your comments wrapper
					} else {
						$("." + ajaxcomment.wrapper).prepend(response.data); // your comments wrapper
					}
				}
				t.createButterbar("提交成功");

				// 提交时为防止重复提交，禁用了提交按钮，完成后需要恢复
				submit_button.prop("disabled", false);

				cancel.style.display = "none";
				cancel.onclick = null;
				t.I("comment_parent").value = "0";
				if (temp && respond) {
					temp.parentNode.insertBefore(respond, temp);
					temp.parentNode.removeChild(temp)
				}
			}
		});
		return false;
	});

	addComment = {
		moveForm: function(commId, parentId, respondId) {
			var t = this,
				div, comm = t.I(commId),
				respond = t.I(respondId),
				cancel = t.I("cancel-comment-reply-link"),
				parent = t.I("comment_parent"),
				post = t.I("comment_post_ID");
			__cancel.text(__cancel_text);
			t.respondId = respondId;
			if (!t.I("wp-temp-form-div")) {
				div = document.createElement("div");
				div.id = "wp-temp-form-div";
				div.style.display = "none";
				respond.parentNode.insertBefore(div, respond)
			}!comm ? (temp = t.I("wp-temp-form-div"), t.I("comment_parent").value = "0", temp.parentNode.insertBefore(respond, temp), temp.parentNode.removeChild(temp)) : comm.parentNode.insertBefore(respond, comm.nextSibling);
			$("body").animate({
				scrollTop: $("#respond").offset().top - 180
			}, 400);
			parent.value = parentId;
			cancel.style.display = "";
			cancel.onclick = function() {
				var t = addComment,
					temp = t.I("wp-temp-form-div"),
					respond = t.I(t.respondId);
				t.I("comment_parent").value = "0";
				if (temp && respond) {
					temp.parentNode.insertBefore(respond, temp);
					temp.parentNode.removeChild(temp);
				}
				this.style.display = "none";
				this.onclick = null;
				return false;
			};
			try {
				t.I("comment").focus();
			} catch (e) {}
			return false;
		},
		I: function(e) {
			return document.getElementById(e);
		},
		clearButterbar: function(e) {
			if ($("#ajax-comment-modal").length > 0) {
				$("#ajax-comment-modal").remove();
			}
		},

		/**
		 *弹窗样式依赖bulma CSS框架
		 *
		 */
		createButterbar: function(message) {
			var t = this;
			t.clearButterbar();
			$("body").append(
				"<div id=\"ajax-comment-modal\" class=\"modal is-active\">" +
				"<div class=\"modal-background\"></div>" +
				"<div class=\"modal-content\">" +
				"<div class=\"modal-entry has-text-centered\"><p class=\"ajax-comment-message\">" + message + "</p></div>" +
				"</div>" +
				"<button class=\"modal-close is-large\" aria-label=\"close\"></button>" +
				"</div>"
			);
			setTimeout("$(\"#ajax-comment-modal\").remove()", 2000);
		}
	};
});