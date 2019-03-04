var curpage = 1;
var hasmore = true;
var footer = false;
var keyword = decodeURIComponent(getQueryString("keyword"));
var gc_id = getQueryString("gc_id");
var b_id = getQueryString("b_id");
var key = getQueryString("key");
var order = getQueryString("order");
var area_id = getQueryString("area_id");
var price_from = getQueryString("price_from");
var price_to = getQueryString("price_to");
var own_shop = getQueryString("own_shop");
var gift = getQueryString("gift");
var groupbuy = getQueryString("groupbuy");
var xianshi = getQueryString("xianshi");
var virtual = getQueryString("virtual");
var ci = getQueryString("ci");
var myDate = new Date;
var searchTimes = myDate.getTime();
$(function () {
    $.animationLeft({valve: "#search_adv", wrapper: ".dstouch-full-mask", scroll: "#list-items-scroll"});
    $("#header").on("click", ".header-inp", function () {
        location.href = WapSiteUrl + "/mall/search.html?keyword=" + keyword
    });
    if (keyword != "") {
        $("#keyword").html(keyword)
    }
    $("#show_style").click(function () {
        if ($("#product_list").hasClass("grid")) {
            $(this).find("span").removeClass("browse-grid").addClass("browse-list");
            $("#product_list").removeClass("grid").addClass("list")
        } else {
            $(this).find("span").addClass("browse-grid").removeClass("browse-list");
            $("#product_list").addClass("grid").removeClass("list")
        }
    });
    $("#sort_default").click(function () {
        if ($("#sort_inner").hasClass("hide")) {
            $("#sort_inner").removeClass("hide")
        } else {
            $("#sort_inner").addClass("hide")
        }
    });
    $("#nav_ul").find("a").click(function () {
        $(this).addClass("current").parent().siblings().find("a").removeClass("current");
        if (!$("#sort_inner").hasClass("hide") && $(this).parent().index() > 0) {
            $("#sort_inner").addClass("hide")
        }
    });
    $("#sort_inner").find("a").click(function () {
        $("#sort_inner").addClass("hide").find("a").removeClass("cur");
        var e = $(this).addClass("cur").text();
        $("#sort_default").html(e + "<i></i>")
    });
    get_list();
    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            get_list()
        }
    });
    search_adv()
});
function get_list() {
    $(".loading").remove();
    if (!hasmore) {
        return false
    }
    hasmore = false;
    param = {};
    param.page = curpage;
    param.pagesize = pagesize;
    if (gc_id != "") {
        param.gc_id = gc_id
    } else if (keyword != "") {
        param.keyword = keyword
    } else if (b_id != "") {
        param.b_id = b_id
    }
    if (key != "") {
        param.key = key
    }
    if (order != "") {
        param.order = order
    }
    $.getJSON(ApiUrl + "/goods/goods_list.html" + window.location.search.replace("?", "&"), param, function (e) {
        if (!e) {
            e = [];
            e.result = [];
            e.result.goods_list = []
        }
        $(".loading").remove();
        curpage++;
        var r = template("home_body", e);
        $("#product_list .goods-secrch-list").append(r);
        hasmore = e.hasmore
    })
}
function search_adv() {
    $.getJSON(ApiUrl + "/index/search_adv.html", function (e) {
        var r = e.result;
        $("#list-items-scroll").html(template("search_items", r));
        if (area_id) {
            $("#area_id").val(area_id)
        }
        if (price_from) {
            $("#price_from").val(price_from)
        }
        if (price_to) {
            $("#price_to").val(price_to)
        }
        if (own_shop) {
            $("#own_shop").addClass("current")
        }
        if (gift) {
            $("#gift").addClass("current")
        }
        if (groupbuy) {
            $("#groupbuy").addClass("current")
        }
        if (xianshi) {
            $("#xianshi").addClass("current")
        }
        if (virtual) {
            $("#virtual").addClass("current")
        }
        if (ci) {
            var i = ci.split("_");
            for (var t in i) {
                $('a[name="ci"]').each(function () {
                    if ($(this).attr("value") == i[t]) {
                        $(this).addClass("current")
                    }
                })
            }
        }
        $("#search_submit").click(function () {
            var e = "?keyword=" + keyword, r = "";
            e += "&area_id=" + $("#area_id").val();
            if ($("#price_from").val() != "") {
                e += "&price_from=" + $("#price_from").val()
            }
            if ($("#price_to").val() != "") {
                e += "&price_to=" + $("#price_to").val()
            }
            if ($("#own_shop")[0].className == "current") {
                e += "&own_shop=1"
            }
            if ($("#gift")[0].className == "current") {
                e += "&gift=1"
            }
            if ($("#groupbuy")[0].className == "current") {
                e += "&groupbuy=1"
            }
            if ($("#xianshi")[0].className == "current") {
                e += "&xianshi=1"
            }
            if ($("#virtual")[0].className == "current") {
                e += "&virtual=1"
            }
            $('a[name="ci"]').each(function () {
                if ($(this)[0].className == "current") {
                    r += $(this).attr("value") + "_"
                }
            });
            if (r != "") {
                e += "&ci=" + r
            }
            window.location.href = WapSiteUrl + "/mall/product_list.html" + e
        });
        $('a[dstype="items"]').click(function () {
            var e = new Date;
            if (e.getTime() - searchTimes > 300) {
                $(this).toggleClass("current");
                searchTimes = e.getTime()
            }
        });
        $('input[dstype="price"]').on("blur", function () {
            if ($(this).val() != "" && !/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test($(this).val())) {
                $(this).val("")
            }
        });
        $("#reset").click(function () {
            $('a[dstype="items"]').removeClass("current");
            $('input[dstype="price"]').val("");
            $("#area_id").val("")
        })
    })
}
function init_get_list(e, r) {
    order = e;
    key = r;
    curpage = 1;
    hasmore = true;
    $("#product_list .goods-secrch-list").html("");
    $("#footer").removeClass("posa");
    get_list()
}