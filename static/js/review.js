//1、 #######################################################评分功能
jQuery(document).ready(function($) {

    if ($("#review-form").length > 0) {

        var txt0 = ["(垃圾)", "(太烂了)", "(比较差)", "(不够好)", "(一般)", "(还可以)", "(还好)", "(很不错)", "(超赞)", "(极品)"];
        var txt1 = ["(很差)", "(较差)", "(一般)", "(很好)", "(优秀)"];
        var txt2 = ["(很差)", "(较差)", "(一般)", "(很好)", "(优秀)"];
        var txt3 = ["(很差)", "(较差)", "(一般)", "(很好)", "(优秀)"];

        // 根据初始html初始化
        $("#rating1 em").text(txt1[$('#item1').data('v') - 1]);

        //表单评分操作 
        $("#doPoint table tr td span small").each(function(index) {


            // 悬停效果
            $(this).mouseover(function() {
                id = index + 1;
                var obj = $(this).parent().parent().next().children("em");
                if (id <= 5) {
                    obj.html(txt1[id - 1]);
                } else if (id > 5 && id <= 10) {
                    id = id - 5;
                    obj.html(txt2[id - 1]);
                } else if (id > 10 && id <= 15) {
                    id = id - 10;
                    obj.html(txt3[id - 1]);
                }

                $(this).parent().removeClass();
                $(this).parent().addClass("star" + id);
                $(this).parent().parent().next().children("strong").html(id);
            });


            $(this).click(function() {

                // 根据鼠标点击，获取并转换每项评分
                id = index + 1;
                if (id <= 5) {
                    $("#pointV1").val(id);
                } else if (id > 5 && id <= 10) {
                    id = id - 5;
                    $("#pointV2").val(id);
                } else if (id > 10 && id <= 15) {
                    id = id - 10;
                    $("#pointV3").val(id);
                }

                $(this).parent().data("v", id);

                var r_1 = parseInt($("#item1").data("v"));
                var num = r_1;

                var integer = parseInt(num);
                var flt = num - integer;
                var fltln = (num.toString()).length - (integer.toString()).length - 1;
                var fltint = (flt.toString()).substring(2, (fltln + 2));
                var fltint = fltint > 0 ? fltint : 0;


                //根据总分 判断显示 
                // $("#myPoint img").attr("src", "statics/star" + integer + ".gif");
                $("#myPoint #num").html(num);
                $("#myPoint small").html("." + fltint);
                $("#myPoint em").html(txt0[integer - 1]);

            });


            // 鼠标移除
            $(this).parent().mouseout(function() {
                var ids = $(this).data("v");
                id = index + 1;
                var obj = $(this).parent().next().children("em");
                if (id <= 5) {
                    obj.html(txt1[ids - 1]);
                } else if (id > 5 && id <= 10) {
                    id = id - 5;
                    obj.html(txt2[ids - 1]);
                } else if (id > 10 && id <= 15) {
                    id = id - 10;
                    obj.html(txt3[ids - 1]);
                }
                $(this).parent().next().children("strong").html(ids);
                $(this).removeClass();
                $(this).addClass("star" + ids);
            });
        });

        // 获取提交评分数据
        $("#review-form").submit(function() {

            var post_id = $("#post-id").val();

            var r_1 = $("#pointV1").val();
            // 平均分
            var num = $("#myPoint #num").text();
            var num = parseFloat(num);

            var content = $("#review-content").val();
            var nonce = $("#_wpnonce").val();

            $.ajax({
                type: "POST",
                datatype: 'json',
                url: ajaxurl,
                data: {
                    '_post_post_id': post_id,
                    'rating': num,
                    'content': content,
                    '_ajax_nonce': nonce,
                    'action': 'wnd_action',
                    'action_name': 'wndt_review'
                },

                //后台返回数据前
                beforeSend: function() {
                    // $('#info').text('数据保存中');
                    $("#review-form :submit").text("保存中……")
                },

                //成功后
                success: function(response) {

                    // 将返回的字符串转为josn
                    data = JSON.parse(response);
                    if (data.status === 1) { //提交评测文章时候的打分操作
                        // alert(data.msg);
                        $('#alert-box').removeClass('is-visible');
                        $('#alert-box').addClass('is-hidden');
                        $("#review-form :submit").text("提交");
                        $(".review-action .review-trigger").text('修改');
                        $('#my-point').remove();

                    } else {
                        alert(data.msg);
                    }


                },

                // 错误
                error: function() {
                    alert('系统错误');
                }

            });

        });
    }

});