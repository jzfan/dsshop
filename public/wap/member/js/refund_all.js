var order_id;
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    $.getJSON(ApiUrl + "/Memberrefund/refund_all_form.html", {
        key: e,
        order_id: getQueryString("order_id")
    }, function(a) {
        a.result.WapSiteUrl = WapSiteUrl;
        $("#order-info-container").html(template("order-info-tmpl", a.result));
        order_id = a.result.order.order_id;
        $("#allow_refund_amount").val("￥" + a.result.order.allow_refund_amount);
        $('input[name="refund_pic"]').ajaxUploadImage({
            url: ApiUrl + "/Memberrefund/upload_pic.html",
            data: {
                key: e
            },
            start: function(e) {
                e.parent().after('<div class="upload-loading"><i></i></div>');
                e.parent().siblings(".pic-thumb").remove()
            },
            success: function(e, a) {
                checkLogin(a.login);
                if (a.code != 10000) {
                    e.parent().siblings(".upload-loading").remove();
                    layer.open({content: '图片尺寸过大！',skin: 'msg',time: 2});
                    return false
                }
                e.parent().after('<div class="pic-thumb"><img src="' + a.result.pic + '"/></div>');
                e.parent().siblings(".upload-loading").remove();
                e.parents("a").next().val(a.result.file_name)
            }
        });
        $(".btn-l").click(function() {
            var a = $("form").serializeArray();
            var r = {};
            r.key = e;
            r.order_id = order_id;
            for (var n = 0; n < a.length; n++) {
                r[a[n].name] = a[n].value
            }
            if (r.buyer_message.length == 0) {
                layer.open({content: '请填写退款说明！',skin: 'msg',time: 2});
                return false
            }
            $.ajax({
                type: "post",
                url: ApiUrl + "/Memberrefund/refund_all_post.html",
                data: r,
                dataType: "json",
                async: false,
                success: function(e) {
                    checkLogin(e.login);
                    if (e.code != 10000) {
                        layer.open({content: e.message, btn: '我知道了'});
                        return false
                    }
                    window.location.href = WapSiteUrl + "/member/member_refund.html"
                }
            })
        })
    })
});