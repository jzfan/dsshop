var curpage = 1;
var hasMore = true;
var footer = false;
var reset = true;
var orderKey = "";
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    if (getQueryString("data-state") != "") {
        $("#filtrate_ul").find("li").has('a[data-state="' + getQueryString("data-state") + '"]').addClass("selected").siblings().removeClass("selected");
    }
    $("#search_btn").click(function() {
        reset = true;
        t();
    });
    $("#fixed_nav").waypoint(function() {
        $("#fixed_nav").toggleClass("fixed");
    },
    {
        offset: "50"
    });
    function t() {
        if (reset) {
            $("#order-list").empty();
            curpage = 1;
            hasMore = true;
            reset = false;
        }
        if(!hasMore){
            return false;
        }

        $(".loading").remove();

        var t = $("#filtrate_ul").find(".selected").find("a").attr("data-state");
        var r = $("#order_key").val();
        
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_list.html?page=" + curpage + "&pagesize=" + pagesize,
            data: {
                key: e,
                state_type: t,
                order_type: $(".cur").attr("order_type"),
                order_key: r
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                curpage++;
                hasMore = e.result.hasmore;
                if (!hasMore) {
                    get_footer()
                }
                if (e.result.order_group_list.length <= 0) {
                    $("#footer").addClass("posa")
                } else {
                    $("#footer").removeClass("posa")
                }
                var t = e;
                t.WapSiteUrl = WapSiteUrl;
                t.ApiUrl = ApiUrl;
                t.key = getCookie("key");
                template.helper("$getLocalTime",function(e) {
                    var t = new Date(parseInt(e) * 1e3);
                    var r = "";
                    r += t.getFullYear() + "年";
                    r += t.getMonth() + 1 + "月";
                    r += t.getDate() + "日 ";
                    r += t.getHours() + ":";
                    r += t.getMinutes();
                    return r
                });
                template.helper("p2f",function(e) {
                    return (parseFloat(e) || 0).toFixed(2);
                });
                template.helper("parseInt",function(e) {
                    return parseInt(e);
                });
                var r = template("order-list-tmpl", t);

                if (!reset) {
                    $("#order-list").append(r);
                } else {
                    $("#order-list").html(r);
                }
            }
        })
    }
    $("#order-list").on("click", ".cancel-order", r);
    $("#order-list").on("click", ".delete-order", o);
    $("#order-list").on("click", ".sure-order", n);
    $("#order-list").on("click", ".evaluation-order", l);
    $("#order-list").on("click", ".evaluation-again-order", d);
    $("#order-list").on("click", ".viewdelivery-order", c);
    $("#order-list").on("click", ".check-payment",
    function() {
        var e = $(this).attr("data-paySn");
        toPay(e, "memberbuy", "pay");
        return false;
    });
    function r() {
        var e = $(this).attr("order_id");
        layer.open({
            content: '确定取消订单？'
            , btn: ['确定','取消']
            , yes: function (index) {
                a(e)
                layer.close(index);
            }
        });
    }
    function a(r) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_cancel.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.code == 10000) {
                    reset = true;
                    t()
                } else {
                    layer.open({content: e.message, btn: '我知道了'});
                }
            }
        })
    }
    function o() {
        var e = $(this).attr("order_id");
        layer.open({
            content: '是否移除订单？电脑端订单回收站可找回订单！'
            , btn: ['确定','取消']
            , yes: function (index) {
                i(e)
                layer.close(index);
            }
        });
    }
    function i(r) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_delete.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.code == 10000) {
                    reset = true;
                    t()
                } else {
                    layer.open({content: e.message, btn: '我知道了'});
                }
            }
        })
    }
    function n() {
        var e = $(this).attr("order_id");
        layer.open({
            content: '确定收到了货物吗？'
            , btn: ['确定','取消']
            , yes: function (index) {
                s(e)
                layer.close(index);
            }
        });
    }
    function s(r) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberorder/order_receive.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.code == 10000) {
                    reset = true;
                    t()
                } else {
                    layer.open({content: e.message, btn: '我知道了'});
                }
            }
        })
    }
    function l() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/member_evaluation.html?order_id=" + e
    }
    function d() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/member_evaluation_again.html?order_id=" + e
    }
    function c() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/order_delivery.html?order_id=" + e
    }
    $("#filtrate_ul").find("a").click(function() {
        $("#filtrate_ul").find("li").removeClass("selected");
        $(this).parent().addClass("selected").siblings().removeClass("selected");
        reset = true;
        window.scrollTo(0, 0);
        t();
    });
    t();
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 10) {
            //console.log($(window).scrollTop() + $(window).height());
            t();
        }
    })
});
function get_footer() {
    if (!footer) {
        footer = true;
        $.ajax({
            url: WapSiteUrl + "/js/footer.js",
            dataType: "script"
        })
    }
}