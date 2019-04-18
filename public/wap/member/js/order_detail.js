$(function() {
    var r = getCookie("key");
    if (!r) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    $.getJSON(ApiUrl + "/Memberorder/order_info.html", {
        key: r,
        order_id: getQueryString("order_id")
    },
    function(t) {
        t.result.order_info.WapSiteUrl = WapSiteUrl;
        $("#order-info-container").html(template("order-info-tmpl", t.result.order_info));
        $(".cancel-order").click(e);
        $(".sure-order").click(o);
        $(".evaluation-order").click(d);
        $(".evaluation-again-order").click(a);
        $(".all_refund_order").click(n);
        $(".goods-refund").click(c);
        $(".goods-return").click(_);
        $(".viewdelivery-order").click(l);
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/get_current_deliver.html",
            data: {
                key: r,
                order_id: getQueryString("order_id")
            },
            dataType: "json",
            success: function(r) {
                checkLogin(r.login);
                var e = r && r.result;
                if (e.deliver_info) {
                    $("#delivery_content").html(e.deliver_info.context);
                    $("#delivery_time").html(e.deliver_info.time)
                }
            }
        })
    });
    function e() {
        var r = $(this).attr("order_id");
        layer.open({
            content: '确定取消订单？'
            , btn: ['确定','取消']
            , yes: function (index) {
                t(r)
                layer.close(index);
            }
        });
    }
    function t(e) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_cancel.html",
            data: {
                order_id: e,
                key: r
            },
            dataType: "json",
            success: function(r) {
                if (r.code == 10000) {
                    window.location.reload()
                }
            }
        })
    }
    function o() {
        var r = $(this).attr("order_id");
        layer.open({
            content: '确定收到了货物吗？'
            , btn: ['确定','取消']
            , yes: function (index) {
                i(r)
                layer.close(index);
            }
        });
    }
    function i(e) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_receive.html",
            data: {
                order_id: e,
                key: r
            },
            dataType: "json",
            success: function(r) {
                if (r.code == 10000) {
                    window.location.reload()
                }
            }
        })
    }
    function d() {
        var r = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/member_evaluation.html?order_id=" + r
    }
    function a() {
        var r = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/member_evaluation_again.html?order_id=" + r
    }
    function n() {
        var r = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/refund_all.html?order_id=" + r
    }
    function l() {
        var r = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/order_delivery.html?order_id=" + r
    }
    function c() {
        var r = $(this).attr("order_id");
        var e = $(this).attr("order_goods_id");
        location.href = WapSiteUrl + "/member/refund.html?order_id=" + r + "&order_goods_id=" + e
    }
    function _() {
        var r = $(this).attr("order_id");
        var e = $(this).attr("order_goods_id");
        location.href = WapSiteUrl + "/member/return.html?order_id=" + r + "&order_goods_id=" + e
    }

    //pay_pic 支付二维码方法事件
    $(document).on("click",".pay_pic img",function(){
        var pay_src=$(this).attr("src");
        $(".shop_payPic img").attr("src",pay_src);
        $(".showBigPay").show();
    });
    $(".show_close").on("click",function(){
        $(".showBigPay").hide();
    });
});