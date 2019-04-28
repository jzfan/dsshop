var page = pagesize;
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
        $("#filtrate_ul").find("li").has('a[data-state="' + getQueryString("data-state") + '"]').addClass("selected").siblings().removeClass("selected")
    }
    $("#search_btn").click(function() {
        reset = true;
        t()
    });
    $("#fixed_nav").waypoint(function() {
        $("#fixed_nav").toggleClass("fixed")
    },
    {
        offset: "50"
    });
    function t() {
        if (reset) {
            curpage = 1;
            hasMore = true
        }
        $(".loading").remove();
        if (!hasMore) {
            return false
        }
        hasMore = false;
        var t = $("#filtrate_ul").find(".selected").find("a").attr("data-state");
        var r = $("#order_key").val();
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberpointorder/orderlist.html?pagesize=" + pagesize + "&page=" + curpage,
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
                hasMore = e.hasmore;
                if (!hasMore) {
                    get_footer()
                }
                if (e.result.order_list.length <= 0) {
                    $("#footer").addClass("posa")
                } else {
                    $("#footer").removeClass("posa")
                }
                var t = e;
                t.WapSiteUrl = WapSiteUrl;
                t.ApiUrl = ApiUrl;
                t.key = getCookie("key");
                template.helper("$getLocalTime",
                function(e) {
                    var t = new Date(parseInt(e) * 1e3);
                    var r = "";
                    r += t.getFullYear() + "年";
                    r += t.getMonth() + 1 + "月";
                    r += t.getDate() + "日 ";
                    r += t.getHours() + ":";
                    r += t.getMinutes();
                    return r
                });
                template.helper("p2f",
                function(e) {
                    return (parseFloat(e) || 0).toFixed(2)
                });
                template.helper("parseInt",
                function(e) {
                    return parseInt(e)
                });
                var r = template("order-list-tmpl", t);
                if (reset) {
                    reset = false;
                    $("#order-list").html(r)
                } else {
                    $("#order-list").append(r)
                }
            }
        })
    }
   
    $("#order-list").on("click", ".sure-order", n);
    $("#order-list").on("click", ".cancel-order", r);
    $("#order-list").on("click", ".view-order", c);

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
            url: ApiUrl + "/Memberpointorder/receiving_order.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.result && e.result == 1) {
                    reset = true;
                    t();
                } else {
                    layer.open({content: e.message,skin: 'msg',time: 2});
                }
            }
        })
    }

    function r() {
        var e = $(this).attr("order_id");
        layer.open({
            content: '确定取消兑换？'
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
            url: ApiUrl + "/Memberpointorder/cancel_order.html",
            data: {
                order_id: r,
                key: e
            },
            dataType: "json",
            success: function(e) {
                if (e.result && e.result == 1) {
                    reset = true;
                    t()
                } else {
                    layer.open({content: e.message,skin: 'msg',time: 2});
                }
            }
        })
    }
	
	
    function c() {
        var e = $(this).attr("order_id");
        location.href = WapSiteUrl + "/member/pointorder_view.html?order_id=" + e;
    }
    $("#filtrate_ul").find("a").click(function() {
        $("#filtrate_ul").find("li").removeClass("selected");
        $(this).parent().addClass("selected").siblings().removeClass("selected");
        reset = true;
        window.scrollTo(0, 0);
        t()
    });
    t();
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            t()
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