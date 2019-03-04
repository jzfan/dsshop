var order_id = getQueryString("order_id");
var map_index_id = "";
var map_list = [];
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
    }
    $.getJSON(ApiUrl + "/Membervrorder/order_info.html", {
        key: e,
        order_id: order_id
    }, function(e) {
        if (e.code != 10000) {
            return;
        }
        e.result.order_info.WapSiteUrl = WapSiteUrl;
        $("#order-info-container").html(template("order-info-tmpl", e.result.order_info));
        $("#buyer_phone").val(e.result.order_info.buyer_phone);
        $(".cancel-order").click(r);
        $(".evaluation-order").click(i);
        $(".all_refund_order").click(o);
        $("#resend").click(t);
        $("#tosend").click(d);
    });

    function r() {
        var e = $(this).attr("order_id");
        layer.open({
            content: '确定取消订单？'
            , btn: ['确定','取消']
            , yes: function (index) {
                a(e);
                layer.close(index);
            }
        });
    }

    function a(r) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Membervrorder/order_cancel.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.code == 10000) {
                    window.location.reload();
                }
            }
        });
    }

    function t() {
        $.animationUp({
            valve: "",
            scroll: ""
        });
        $("#buyer_phone").on("blur", function() {
            if ($(this).val() != "" && !/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test($(this).val())) {
                $(this).val(/\d+/.exec($(this).val()))
            }
        });
    }

    function d() {
        var r = $("#buyer_phone").val();
        $.ajax({
            type: "post",
            url: ApiUrl + "/Membervrorder/resend.html",
            data: {
                order_id: order_id,
                buyer_phone: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.code == 10000) {
                    $(".dstouch-bottom-mask").addClass("down").removeClass("up")
                } else {
                    $(".rpt_error_tip").html(e.message).show()
                }
            }
        });
    }

    function i() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/member_vr_evaluation.html?order_id=" + e
    }

    function o() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/refund_all.html?order_id=" + e
    }
});