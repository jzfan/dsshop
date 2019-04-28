$(function() {
    template.helper("isEmpty", function(t) {
        for (var a in t) {
            return false
        }
        return true
    });
    template.helper("decodeURIComponent", function(t) {
        return decodeURIComponent(t)
    });
    var t = getCookie("key");
    if (!t) {
        var a = decodeURIComponent(getCookie("goods_cart"));
        if (a != null) {
            var e = a.split("|")
        } else {
            e = {}
        }
        var r = new Array;
        var o = 0;
        if (e.length > 0) {
            for (var i = 0; i < e.length; i++) {
                var n = e[i].split(",");
                if (isNaN(n[0]) || isNaN(n[1]))
                    continue;
                data = getGoods(n[0], n[1]);
                if ($.isEmptyObject(data))
                    continue;
                if (r.length > 0) {
                    var c = false;
                    for (var s = 0; s < r.length; s++) {
                        if (r[s].store_id == data.store_id) {
                            r[s].goods.push(data);
                            c = true
                        }
                    }
                    if (!c) {
                        var l = {};
                        l.store_id = data.store_id;
                        l.store_name = data.store_name;
                        var a = new Array;
                        a = [data];
                        l.goods = a;
                        r = [l]
                    }
                } else {
                    var l = {};
                    l.store_id = data.store_id;
                    l.store_name = data.store_name;
                    var a = new Array;
                    a = [data];
                    l.goods = a;
                    r = [l]
                }
                o += parseFloat(data.goods_sum)
            }
        }
        var d = {cart_list: r, sum: o.toFixed(2), cart_count: e.length, check_out: false};
        d.WapSiteUrl = WapSiteUrl;
        var u = template("cart-list", d);
        $("#cart-list").addClass("no-login");
        if (d.cart_list.length == 0) {
            get_footer()
        }
        $("#cart-list-wp").html(u);
        $(".goto-settlement,.goto-shopping").parent().hide();
        $(".goods-del").click(function() {
            var t = $(this);
            layer.open({
                content: '确认删除吗？'
                , btn: ['确定', '取消']
                , yes: function (index) {
                    var a = t.attr("cart_id");
                    for (var r = 0; r < e.length; r++) {
                        var o = e[r].split(",");
                        if (o[0] == a) {
                            e.splice(r, 1);
                            break
                        }
                    }
                    addCookie("goods_cart", e.join("|"));
                    addCookie("cart_count", e.length);
                    location.reload()
                    layer.close(index);
                }
            });
        });
        $(".minus").click(function() {
            var t = $(this).parents(".cart-litemw-cnt");
            var a = t.attr("cart_id");
            for (var r = 0; r < e.length; r++) {
                var o = e[r].split(",");
                if (o[0] == a) {
                    if (o[1] == 1) {
                        return false
                    }
                    o[1] = parseInt(o[1]) - 1;
                    e[r] = o[0] + "," + o[1];
                    t.find(".buy-num").val(o[1])
                }
            }
            addCookie("goods_cart", e.join("|"))
        });
        $(".add").click(function() {
            var t = $(this).parents(".cart-litemw-cnt");
            var a = t.attr("cart_id");
            for (var r = 0; r < e.length; r++) {
                var o = e[r].split(",");
                if (o[0] == a) {
                    o[1] = parseInt(o[1]) + 1;
                    e[r] = o[0] + "," + o[1];
                    t.find(".buy-num").val(o[1])
                }
            }
            addCookie("goods_cart", e.join("|"))
        })
    } else {
        function p() {
            $.ajax({url: ApiUrl + "/Membercart/cart_list.html", type: "post", dataType: "json", data: {key: t}, success: function(t) {
	            if (checkLogin(t.login)) {
	                if (t.code == 10000) {
	                    if (t.result.cart_list.length == 0) {
	                        addCookie("cart_count", 0)
	                    }
	                    var a = t.result;
	                    a.WapSiteUrl = WapSiteUrl;
	                    a.check_out = true;
	                    template.helper("$getLocalTime", function(t) {
	                        var a = new Date(parseInt(t) * 1e3);
	                        var e = "";
	                        e += a.getFullYear() + "年";
	                        e += a.getMonth() + 1 + "月";
	                        e += a.getDate() + "日 ";
	                        return e
	                    });
	                    var e = template("cart-list", a);
	                    if (a.cart_list.length == 0) {
	                        get_footer()
	                    }
	                    $("#cart-list-wp").html(e);
	                    $(".goods-del").click(function() {
	                        var t = $(this).attr("cart_id");
	                        layer.open({
	                            content: '确认删除吗？'
	                            , btn: ['确定', '取消']
	                            , yes: function (index) {
	                                f(t)
	                                layer.close(index);
	                            }
	                        });
	                    });
	                    $(".minus").click(h);
	                    $(".add").click(g);
	                    $(".buynum").blur(m);
	                    $.animationUp();
	                    $(".dstouch-voucher-list").on("click", ".btn", function() {
	                        getFreeVoucher($(this).attr("data-tid"))
	                    });
	                    $(".store-activity").click(function() {
	                        $(this).css("height", "auto")
	                    })
	                } else {
	                    alert(t.message)
	                }
	            }
	        }})
        }
        p();
        function f(a) {
            $.ajax({url: ApiUrl + "/Membercart/cart_del.html", type: "post", data: {key: t, cart_id: a}, dataType: "json", success: function(t) {
                    if (checkLogin(t.login)) {
                        if (t.code == 10000) {
                            p();
                            delCookie("cart_count");
                            getCartCount()
                        } else {
                            alert(t.message)
                        }
                    }
                }})
        }
        function h() {
            var t = this;
            _(t, "minus")
        }
        function g() {
            var t = this;
            _(t, "add")
        }
        function _(a, e) {
            var r = $(a).parents(".cart-litemw-cnt");
            var o = r.attr("cart_id");
            var i = r.find(".buy-num");
            var n = r.find(".goods-price em");
            var c = parseInt(i.val());
            var s = 1;
            if (e == "add") {
                s = parseInt(c + 1)
            } else {
                if (c > 1) {
                    s = parseInt(c - 1)
                } else {
                    return false
                }
            }
            $(".pre-loading").removeClass("hide");
            $.ajax({url: ApiUrl + "/Membercart/cart_edit_quantity.html", type: "post", data: {key: t, cart_id: o, quantity: s}, dataType: "json", success: function(t) {
                if (checkLogin(t.login)) {
                    if (t.code == 10000) {
                        i.val(s);
                        n.html("" + t.result.goods_price + "");
                        calculateTotalPrice()
                    } else {
                        layer.open({content: t.message, btn: '我知道了'});
                    }
                    $(".pre-loading").addClass("hide")
                }
            }})
        }
        $("#cart-list-wp").on("click", ".check-out > a", function() {
            if (!$(this).parent().hasClass("ok")) {
                return false
            }
            var t = [];
            $(".cart-litemw-cnt").each(function() {
                if ($(this).find('input[name="cart_id"]').prop("checked")) {
                    var a = $(this).find('input[name="cart_id"]').val();
                    var e = parseInt($(this).find(".value-box").find("input").val());
                    var r = a + "|" + e;
                    t.push(r)
                }
            });
            var a = t.toString();
            window.location.href = WapSiteUrl + "/order/buy_step1.html?ifcart=1&cart_id=" + a
        });
        $.sValid.init({rules: {buynum: "digits"}, messages: {buynum: "请输入正确的数字"}, callback: function(t, a, e) {
                if (t.length > 0) {
                    var r = "";
                    $.map(a, function(t, a) {
                        r += "<p>" + t + "</p>"
                    });
                    layer.open({content: r, btn: '我知道了'});
                }
            }});
        function m() {
            $.sValid()
        }}
    $("#cart-list-wp").on("click", ".store_checkbox", function() {
        $(this).parents(".dstouch-cart-container").find('input[name="cart_id"]').prop("checked", $(this).prop("checked"));
        calculateTotalPrice()
    });
    $("#cart-list-wp").on("click", ".all_checkbox", function() {
        $("#cart-list-wp").find('input[type="checkbox"]').prop("checked", $(this).prop("checked"));
        calculateTotalPrice()
    });
    $("#cart-list-wp").on("click", 'input[name="cart_id"]', function() {
        var store_num = $(this).parents(".dstouch-cart-container").find('input[name="cart_id"]').length;//当前店铺商品的数量
        var store_checked_num = $(this).parents(".dstouch-cart-container").find('input[name="cart_id"]:checked').length;//当前店铺商品勾选数量
        if (store_checked_num == store_num) {
            $(this).parents(".dstouch-cart-container").find('.store_checkbox').prop("checked", true);
        } else {
            $(this).parents(".dstouch-cart-container").find('.store_checkbox').prop("checked", false);
        }
        calculateTotalPrice()
    })
});
function show_all_checkbox()
{
    var all_num = $("#cart-list-wp").find('input[name="cart_id"]').length;//所有店铺商品数量
    var all_checked_num = $("#cart-list-wp").find('input[name="cart_id"]:checked').length;//所有店铺商品勾选数量
    if (all_checked_num == all_num) {
        $(".all_checkbox").prop("checked", true);
    } else {
        $(".all_checkbox").prop("checked", false);
    }
}
function calculateTotalPrice() {
    show_all_checkbox();
    var t = parseFloat("0.00");
    var f = parseFloat("0.00");
    $(".cart-litemw-cnt").each(function() {
        if ($(this).find('input[name="cart_id"]').prop("checked")) {
            t += parseFloat($(this).find(".goods-price").find("em").html()) * parseInt($(this).find(".value-box").find("input").val());
            f += parseFloat($(this).find(".goods-price").find("b").html()) * parseInt($(this).find(".value-box").find("input").val());
        }
    });
    $(".total-money").find("em").html(t.toFixed(2));
    $(".total-money").find("b").html(f.toFixed(2));
    check_button();
    return true
}
function getGoods(t, a) {
    var e = {};
    $.ajax({type: "get", url: ApiUrl + "/Goods/goods_detail.html?goods_id=" + t, dataType: "json", async: false, success: function(r) {
            if (r.code != 10000) {
                return false
            }
            var o = r.result.goods_image.split(",");
            e.cart_id = t;
            e.goods_id = t;
            e.goods_name = r.result.goods_info.goods_name;
            e.goods_price = r.result.goods_info.goods_price;
            e.goods_num = a;
            e.goods_image_url = o[0];
            e.goods_sum = (parseInt(a) * parseFloat(r.result.goods_info.goods_price)).toFixed(2)
        }});
    return e
}
function get_footer() {
    footer = true;
    $.ajax({url: WapSiteUrl + "/js/footer.js", dataType: "script"})
}
function check_button() {
    var t = false;
    $('input[name="cart_id"]').each(function() {
        if ($(this).prop("checked")) {
            t = true
        }
    });
    if (t) {
        $(".check-out").addClass("ok")
    } else {
        $(".check-out").removeClass("ok")
    }
}