var order_id, order_goods_id, goods_pay_price;
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    $.getJSON(ApiUrl + "/Memberrefund/refund_form.html", {key: e, order_id: getQueryString("order_id"), order_goods_id: getQueryString("order_goods_id")}, function(r) {
        checkLogin(r.login);
        r.result.WapSiteUrl = WapSiteUrl;
        $("#order-info-container").html(template("order-info-tmpl", r.result));
        order_id = r.result.order.order_id;
        order_goods_id = r.result.goods.order_goods_id;
        var a = "";
        for (var o in r.result.reason_list) {
            a += '<option value="' + r.result.reason_list[o].reason_id + '">' + r.result.reason_list[o].reason_info + "</option>"
        }
        $("#refundReason").append(a);
        goods_pay_price = r.result.goods.goods_pay_price;
        $('input[name="refund_amount"]').val(goods_pay_price);
        $("#returnAble").html("￥" + goods_pay_price);
        $('input[name="refund_pic"]').ajaxUploadImage({url: ApiUrl + "/Memberrefund/upload_pic.html", data: {key: e}, start: function(e) {
                e.parent().after('<div class="upload-loading"><i></i></div>');
                e.parent().siblings(".pic-thumb").remove()
            }, success: function(e, r) {
                checkLogin(r.login);
                if (r.code != 10000) {
                    e.parent().siblings(".upload-loading").remove();
                    layer.open({content: '图片尺寸过大',skin: 'msg',time: 2});
                    return false
                }
                e.parent().after('<div class="pic-thumb"><img src="' + r.result.pic + '"/></div>');
                e.parent().siblings(".upload-loading").remove();
                e.parents("a").next().val(r.result.file_name)
            }});
        $(".btn-l").click(function() {
            var r = $("form").serializeArray();
            var a = {};
            a.key = e;
            a.order_id = order_id;
            a.order_goods_id = order_goods_id;
            a.refund_type = 1;
            for (var o = 0; o < r.length; o++) {
                a[r[o].name] = r[o].value
            }
            if (parseFloat(a.refund_amount) > parseFloat(goods_pay_price) || isNaN(parseFloat(a.refund_amount)) || parseFloat(a.refund_amount) == 0) {
                layer.open({content: '退款金额不能为空，或不能超过可退金额',skin: 'msg',time: 2});
                return false
            }
            if (a.buyer_message.length == 0) {
                
                return false
            }
            $.ajax({type: "post", url: ApiUrl + "/Memberrefund/refund_post.html", data: a, dataType: "json", async: false, success: function(e) {
                    checkLogin(e.login);
                    if (e.code != 10000) {
                        layer.open({content: e.message,skin: 'msg',time: 2});
                        return false
                    }
                    window.location.href = WapSiteUrl + "/member/member_refund.html"
                }})
        })
    })
});