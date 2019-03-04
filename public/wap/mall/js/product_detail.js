var goods_id = getQueryString("goods_id");
var pintuangroup_share_id = getQueryString("pintuangroup_share_id");
var map_list = [];
var map_index_id = "";

$(function() {
	//记录浏览历史
	$.get(ApiUrl + "/Goods/addbrowse/gid/"+getQueryString("goods_id"));
    var e = getCookie("key");
    var t = function(e, t) {
        e = parseFloat(e) || 0;
        if (e < 1) {
            return ""
        }
        var o = new Date;
        o.setTime(e * 1e3);
        var a = "" + o.getFullYear() + "-" + (1 + o.getMonth()) + "-" + o.getDate();
        if (t) {
            a += " " + o.getHours() + ":" + o.getMinutes() + ":" + o.getSeconds()
        }
        return a
    };
    var o = function(e, t) {
        e = parseInt(e) || 0;
        t = parseInt(t) || 0;
        var o = 0;
        if (e > 0) {
            o = e
        }
        if (t > 0 && o > 0 && t < o) {
            o = t
        }
        return o
    };
    template.helper("isEmpty",
            function(e) {
                for (var t in e) {
                    return false
                }
                return true
            });
    function a() {
        var e = $("#mySwipe")[0];
        window.mySwipe = Swipe(e, {
            continuous: false,
            stopPropagation: true,
            callback: function(e, t) {
                $(".goods-detail-turn").find(".now_page").text(e+1);
            }
        });
    }
    r(goods_id);
    function i(e, t) {
        $(e).addClass("current").siblings().removeClass("current");
        var o = $(".spec").find("a.current");
        var a = [];
        $.each(o,
                function(e, t) {
                    a.push(parseInt($(t).attr("specs_value_id")) || 0)
                });
        var i = a.sort(function(e, t) {
            return e - t;
        }).join("|");
        goods_id = t.spec_list[i];
        r(goods_id);
    }
    function s(e, t) {
        var o = e.length;
        while (o--) {
            if (e[o] === t) {
                return true;
            }
        }
        return false;
    }
    $.sValid.init({
        rules: {
            buynum: "digits"
        },
        messages: {
            buynum: "请输入正确的数字"
        },
        callback: function (e, t, o) {
            if (e.length > 0) {
                var a = "";
                $.map(t,function (e, t) {a += "<p>" + e + "</p>"});
                layer.open({content: a, skin: 'msg', time: 2});
            }
        }
    });
    function n() {
        $.sValid();
    }
    function r(r) {
        $.ajax({
            url: ApiUrl + "/goods/goods_detail",
            type: "get",
            data: {
                goods_id: r,
                key: e,
                pintuangroup_share_id:pintuangroup_share_id
            },
            dataType: "json",
            success: function(e) {
                var l = e.result;
                if (e.code==10000) {
                    if (l.goods_image) {
                        var d = l.goods_image.split(",");
                        l.goods_image = d
                    } else {
                        l.goods_image = []
                    }
                    if (l.goods_info.spec_name) {
                        var c = $.map(l.goods_info.spec_name,
                                function(e, t) {
                                    var o = {};
                                    o["goods_spec_id"] = t;
                                    o["goods_spec_name"] = e;
                                    if (l.goods_info.spec_value) {
                                        $.map(l.goods_info.spec_value,
                                                function(e, a) {
                                                    if (t == a) {
                                                        o["goods_spec_value"] = $.map(e,
                                                                function(e, t) {
                                                                    var o = {};
                                                                    o["specs_value_id"] = t;
                                                                    o["specs_value_name"] = e;
                                                                    return o
                                                                })
                                                    }
                                                });
                                        return o
                                    } else {
                                        l.goods_info.spec_value = []
                                    }
                                });
                        l.goods_map_spec = c
                    } else {
                        l.goods_map_spec = []
                    }
                    if (l.goods_info.is_virtual == "1") {
                        l.goods_info.virtual_indate_str = t(l.goods_info.virtual_indate, true);
                        l.goods_info.buyLimitation = o(l.goods_info.virtual_limit, l.goods_info.upper_limit)
                    }
                    if (l.goods_info.is_presell == "1") {
                        l.goods_info.presell_deliverdate_str = t(l.goods_info.presell_deliverdate)
                    }
                    var _ = template("product_detail", l);
                    $("#product_detail_html").html(_);
                    var _ = template("product_detail_sepc", l);
                    $("#product_detail_spec_html").html(_);
                    var _ = template("product_detail_foot", l);
                    $("#product_detail_foot_html").html(_);
                    var _ = template("voucher_script", l);
                    $("#voucher_html").html(_);
                    if (getCookie("cart_count")) {
                        if (getCookie("cart_count") > 0) {
                            $("#cart_count").html("<sup>" + getCookie("cart_count") + "</sup>")
                        }
                    }
                    a();
                    //给轮播图高度
                    $('.goods-detail-top').css('height',$(window).width());
                    //详情图懒加载
                    $(window).scroll(function() {
                        if($("img.lazyload").length==0){
                            return;
                        }
                        if($('.goods-body .more.wait').length>0 && $('.goods-body').height()>=600){
                            $('.goods-body .more').show().removeClass('wait');
                        }
                        if(($(window).scrollTop()+$(window).height())>$("img.lazyload").eq(0).offset().top){
                            $("img.lazyload").eq(0).attr('src',$("img.lazyload").eq(0).attr('data-original')).removeClass('lazyload').removeAttr('data-original');
                        }
                    })
                    $('.goods-body .more').click(function(){
                        $('.goods-body').css('max-height','initial');
                        $('.goods-body .more').remove();
                    });
                    
                    $(".pddcp-arrow").click(function() {
                        $(this).parents(".pddcp-one-wp").toggleClass("current")
                    });
                    var p = {};
                    p["spec_list"] = l.spec_list;
                    $(".spec a").click(function() {
                        var e = this;
                        i(e, p)
                    });
                    $(".minus").click(function() {
                        var e = $(".buy-num").val();
                        if (e > 1) {
                            $(".buy-num").val(parseInt(e - 1))
                        }
                    });
                    $(".add").click(function() {
                        var e = parseInt($(".buy-num").val());
                        if (e < l.goods_info.goods_storage) {
                            $(".buy-num").val(parseInt(e + 1))
                        }
                    });
                    if (l.goods_info.is_goodsfcode == "1") {
                        $(".minus").hide();
                        $(".add").hide();
                        $(".buy-num").attr("readOnly", true)
                    }
                    $(".pd-collect").click(function() {
                        if ($(this).hasClass("favorate")) {
                            if (dropFavoriteGoods(r))
                                $(this).removeClass("favorate")
                        } else {
                            if (favoriteGoods(r))
                                $(this).addClass("favorate")
                        }
                    });
                    $(".add-cart").click(function() {
                        if(!$('#product_detail_spec_html').hasClass('up')){
                            return;
                        }
                        var e = getCookie("key");
                        var t = parseInt($(".buy-num").val());
                        if (!e) {
                            var o = getCookie("goods_cart");
                            if (o == null) {
                                o = ""
                            }else{
								o = decodeURIComponent(o);
							}
                            if (r < 1) {//非法商品ID，不允许添加
                                //show_tip();
                                return false
                            }
                            var a = 0;
                            if (!o) {
                                o = r + "," + t;
                                //a = 1
                            } else {
                                var i = o.split("|");
                                for (var n = 0; n < i.length; n++) {
                                    var l = i[n].split(",");
                                    if (s(l, r)) {
                                        show_tip();
                                        return false
                                    }
                                }
                                o += "|" + r + "," + t;
                                //a = i.length + 1
                            }
                            addCookie("goods_cart", o);
                            //addCookie("cart_count", a);
                            //show_tip();
                            //getCartCount();
                            //$("#cart_count,#cart_count1").html("<sup>" + a + "</sup>");
                            //return false
                        }
                            $.ajax({
                                url: ApiUrl + "/Membercart/cart_add.html",
                                data: {
                                    key: e,
                                    goods_id: r,
                                    quantity: t
                                },
                                type: "post",
                                success: function(e) {
                                    var t = $.parseJSON(e);

                                        if (t.code==10000) {
                                            show_tip();
                                            if(getCookie("key")){
                                                delCookie("cart_count");
                                                getCartCount();

                                            }else{
                                                var a = 0;
                                                if(getCookie("cart_count")!=null){
                                                    a=parseInt(getCookie("cart_count"));
                                                }
                                                a=a+1;
                                                delCookie("cart_count");
                                                addCookie("cart_count", a);
                                            }
                                            $("#cart_count").html("<sup>" + getCookie("cart_count") + "</sup>")
                                        } else {
                                            layer.open({content: t.message, btn: '我知道了'});
                                        }

                                }
                            })

                    });
                    if (l.goods_info.is_virtual == "1") {
                        $(".buy-now").click(function() {
                            if(!$('#product_detail_spec_html').hasClass('up')){
                                return;
                            }
                            var e = getCookie("key");
                            if (!e) {
                                window.location.href = WapSiteUrl + "/member/login.html";
                                return false
                            }
                            var t = parseInt($(".buy-num").val()) || 0;
                            if (t < 1) {
                                layer.open({content: '参数错误！',skin: 'msg',time: 2});
                                return
                            }
                            if (t > l.goods_info.goods_storage) {
                                layer.open({content: '库存不足！',skin: 'msg',time: 2});
                                return
                            }
                            if (l.goods_info.buyLimitation > 0 && t > l.goods_info.buyLimitation) {
                                layer.open({content: '超过限购数量！',skin: 'msg',time: 2});
                                return
                            }
                            var o = {};
                            o.key = e;
                            o.cart_id = r;
                            o.quantity = t;
                            $.ajax({
                                type: "post",
                                url: ApiUrl + "/Membervrbuy/buy_step1.html",
                                data: o,
                                dataType: "json",
                                success: function(e) {
                                    if (e.code!=10000) {
                                        layer.open({content: e.message,skin: 'msg',time: 2});
                                    } else {
                                        location.href = WapSiteUrl + "/order/vr_buy_step1.html?goods_id=" + r + "&quantity=" + t
                                    }
                                }
                            })
                        })
                    } else {
                        $(".buy-now").click(function() {
                            if(!$('#product_detail_spec_html').hasClass('up')){
                                return;
                            }
                            var e = getCookie("key");
                            if (!e) {
                                window.location.href = WapSiteUrl + "/member/login.html"
                            } else {
                                var t = parseInt($(".buy-num").val()) || 0;
                                if (t < 1) {
                                    layer.open({content: '参数错误！',skin: 'msg',time: 2});
                                    return
                                }
                                if (t > l.goods_info.goods_storage) {
                                    layer.open({content: '库存不足！',skin: 'msg',time: 2});
                                    return
                                }
                                var o = {};
                                o.key = e;
                                o.cart_id = r + "|" + t;
                                $.ajax({
                                    type: "post",
                                    url: ApiUrl + "/Memberbuy/buy_step1.html",
                                    data: o,
                                    dataType: "json",
                                    success: function(e) {
                                        if (e.code!=10000) {
                                            layer.open({content: e.message,skin: 'msg',time: 2});
                                        } else {
                                            location.href = WapSiteUrl + "/order/buy_step1.html?goods_id=" + r + "&buynum=" + t
                                        }
                                    }
                                })
                            }
                        })
                    }

                    //立刻拼团,开团
                    if (l.goods_info.is_virtual == "1") {
                        $(".pintuan-now").click(function () {
                            layer.open({
                                content: '虚拟产品不能参加拼团'
                                , skin: 'msg'
                                , time: 2 //2秒后自动关闭
                            });
                        });
                    }else {
                        $("#pintuan-now").click(function(){
                            $('#product_detail_spec_html').removeClass('down').addClass('up')
                            $("#product_detail_foot_html .pintuan-now").attr('fieldid',$(this).attr('fieldid'))
                            $("#product_detail_foot_html .pintuan-now").text(ds_lang.join_the_group)
                            $("#product_detail_foot_html").css('z-index','30')
                        });
                        $(".pintuan-now").click(function () {
                            if(!$('#product_detail_spec_html').hasClass('up')){
                                return;
                            }
                            var e = getCookie("key");
                            if (!e) {
                                window.location.href = WapSiteUrl + "/member/login.html"
                            } else {
                                var t = parseInt($(".buy-num").val()) || 0;
                                if (t < 1) {
                                    layer.open({content: '参数错误！', skin: 'msg', time: 2});
                                    return
                                }
                                if (t > l.goods_info.goods_storage) {
                                    layer.open({content: '库存不足！', skin: 'msg', time: 2});
                                    return
                                }
                                //获取拼团ID 用于拼团下单
                                var pintuan_id = l.goods_info.pintuan_id
                                if (pintuan_id < 1) {
                                    layer.open({content: '拼团ID参数错误！', skin: 'msg', time: 2});
                                    return
                                }
                                //获取当前购买的发起拼团ID,用于参团。
                                var pintuangroup_id = parseInt($(this).attr('fieldid'));
                                //判断当前购买数量是否小于购买拼团的数量限制
                                var pintuan_limit_quantity = l.goods_info.pintuan_limit_quantity
                                if (t > pintuan_limit_quantity) {
                                    layer.open({content: '拼团最多只能购买'+pintuan_limit_quantity+'件商品', skin: 'msg', time: 2});
                                    return
                                }

                                var o = {};
                                o.key = e;
                                o.cart_id = r + "|" + t;


                                $.ajax({
                                    type: "post",
                                    url: ApiUrl + "/Memberbuy/buy_step1.html?pintuangroup_id=0",
                                    data: o,
                                    dataType: "json",
                                    success: function (e) {
                                        if (e.code != 10000) {
                                            layer.open({content: e.message, skin: 'msg', time: 2});
                                        } else {
                                            location.href = WapSiteUrl + "/order/buy_step1.html?goods_id=" + r + "&buynum=" + t + "&pintuan_id=" + pintuan_id + "&pintuangroup_id="+pintuangroup_id
                                        }
                                    }
                                })
                            }
                        })
                    }


                } else {
                    layer.open({
                        content: e.message + "！<br>请返回上一页继续操作…"
                        , btn: ['返回', '取消']
                        , yes: function (index) {
                            history.back()
                            layer.close(index);
                        }
                    });
                }
                $("#buynum").blur(n);
                $.animationUp({
                    valve: ".animation-up,#goods_spec_selected",
                    wrapper: "#product_detail_spec_html",
                    scroll: "#product_roll",
                    start: function() {
                        $("#product_detail_foot_html").css('z-index','30')
                    },
                    close: function() {
                        $("#product_detail_foot_html").css('z-index','1')
                        $("#product_detail_foot_html .pintuan-now").attr('fieldid','0')
                        $("#product_detail_foot_html .pintuan-now").text(ds_lang.immediately_open_regiment)
                    }
                });
                $.animationUp({
                    valve: "#getVoucher",
                    wrapper: "#voucher_html",
                    scroll: "#voucher_roll"
                });
                $("#voucher_html").on("click", ".btn",
                        function() {
                            getFreeVoucher($(this).attr("data-tid"))
                        });
            }
        })
    }
    $("#product_detail_html").on("click", "#get_area_selected",
            function() {
                $.areaSelected({
                    success: function(e) {
                        $("#get_area_selected_name").html(e.area_info);
                        var t = e.area_id_2 == 0 ? e.area_id_1 : e.area_id_2;
                        $.getJSON(ApiUrl + "/Goods/calc.html", {
                            goods_id: goods_id,
                            area_id: t
                        },
                        function(e) {
                            $("#get_area_selected_whether").html(e.result.if_deliver_cn);
                            $("#get_area_selected_content").html(e.result.content);
                            if (!e.result.if_deliver) {
                                $(".buy-handle").addClass("no-buy")
                            } else {
                                $(".buy-handle").removeClass("no-buy")
                            }
                        })
                    }
                })
            });
    $("body").on("click", "#goodsBody,#goodsBody1",
            function() {
                window.location.href = WapSiteUrl + "/mall/product_info.html?goods_id=" + goods_id
            });
    $("body").on("click", "#goodsEvaluation,#goodsEvaluation1",
            function() {
                window.location.href = WapSiteUrl + "/mall/product_eval_list.html?goods_id=" + goods_id
            });
});
function show_tip() {
    var e = $(".goods-pic > img").clone().css({
        "z-index": "999",
        height: "3rem",
        width: "3rem"
    });
    e.fly({
        start: {
            left: $(".goods-pic > img").offset().left,
            top: $(".goods-pic > img").offset().top - $(window).scrollTop()
        },
        end: {
            left: $("#cart_count").offset().left + 40,
            top: $("#cart_count").offset().top - $(window).scrollTop(),
            width: 0,
            height: 0
        },
        onEnd: function() {
            e.remove()
        }
    })
}