$(function() {
    var r = getCookie("key");
    if (!r) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    $.getJSON(ApiUrl + "/Memberpointorder/order_info.html", {
        key: r,
        order_id: getQueryString("order_id")
    },
    function(t) {
        t.result.WapSiteUrl = WapSiteUrl;
        $("#order-info-container").html(template("order-info-tmpl", t.result));
        $(".cancel-order").click(e);
        $(".sure-order").click(o);        
        
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
            url: ApiUrl + "/Memberpointorder/cancel_order.html",
            data: {
                order_id: e,
                key: r
            },
            dataType: "json",
            success: function(r) {
                if (r.result && r.result == 1) {
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
            url: ApiUrl + "/Memberpointorder/receiving_order.html",
            data: {
                order_id: e,
                key: r
            },
            dataType: "json",
            success: function(r) {
                if (r.result && r.result == 1) {
                    window.location.reload()
                }
            }
        })
    }
   
});