var goods_id = getQueryString("goods_id");
var fromTo = getQueryString("fromTo");
var job_id = getQueryString("job_id");
var pintuangroup_share_id = getQueryString("pintuangroup_share_id");
var map_list = [];
var map_index_id = "";
var tuan_sku;
var goodsid;
var isOne = true;
var miaomi = 0;
var needMiammi = 0;
var e = getCookie("key");
var address = sessionStorage.getItem("spike_address_id");
var uname = "";
var uphone = "";
var pay_sn = "";
var payment_code = "";
$(function() {
	//判断用户是否登录
	if(!e){
		//尚未登录
		$(".buy-now").addClass("no");
	}else{
	    //获取我的总积分
	    $.getJSON(ApiUrl + '/Member/my_asset.html', { 'key': e, 'fields': 'miaomi' }, function(es) {
	        miaomi = es.result.miaomi;
	        $("#my_miaomi").text(miaomi);
	    });
	    
	    //获取我的默认地址
	    $.getJSON(ApiUrl + '/Memberaddress/address_list.html', { 'key': key}, function(e){
	    	if(e.result.address_list.length == 0){
	    		$(".getUads_infos").hide();
	    		$(".getUadsNo").show();
	    		$(".getUadsNo").text("选择地址");
	    	}else{
		    	//获取默认地址ID
		    	for (var i=0;i < e.result.address_list.length; i++) {
		    		if(e.result.address_list[i].address_is_default == "1"){
		    			address = e.result.address_list[i].address_id;
		    			$(".uname").text(e.result.address_list[i].address_mob_phone);
				    	$(".uphone").text(e.result.address_list[i].address_realname);
		    			$(".show_address").text(e.result.address_list[i].area_info);
		    		}else{
			    		address = e.result.address_list[0].address_id;
			    		$(".uname").text(e.result.address_list[0].address_realname);
				    	$(".uphone").text(e.result.address_list[0].address_mob_phone);
		    			$(".show_address").text(e.result.address_list[0].area_info);
			    	}
		    	}
	    	}
	    });
		
    }
    

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
                $(".goods-detail-turn").find(".now_page").text(e + 1);
            }
        });
    }

    r(goods_id);
    //SKU事件在这里触发
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
        callback: function(e, t, o) {
            if (e.length > 0) {
                var a = "";
                $.map(t, function(e, t) { a += "<p>" + e + "</p>" });
                layer.open({ content: a, skin: 'msg', time: 2 });
            }
        }
    });

    function n() {
        $.sValid();
    }

    //获取数据
    function r(r) {
        $.ajax({
            //url: ApiUrl + "/goods/goods_detail",
            url: ApiUrl + "/api.seckillgoods/get/",
            type: "get",
            data: {
                goods_id: r,
                key: e,
                job_id:job_id,
                pintuangroup_share_id: pintuangroup_share_id
            },
            dataType: "json",
            success: function(e) {
//              console.log(e);
                
                var d = e.image.split(",");
                if (d.length > 1) {
                    for (var i = 0; i < d.length; i++) {
                        $("#mySwipe ul").append('<li><img src="' + d[i] + '" onerror="imgError(this)"/></li>');
                        $(".goods-detail-turn").show();
                        $(".goods-detail-turn .total_page").text(d.length);
                    }
                } else {
                    $(".goods-detail-turn").hide();
                    $("#mySwipe ul").append('<li><img src="' + d[i] + '" onerror="imgError(this)"/></li>');
                }
                
                
                needMiammi = e.mi;
                $(".good_name").text(e.name);
                $(".goods_advword").text(e.goods_advword); //商品介绍
                $("#good_price").text(e.price);
                $("#good_miaomi").text(e.mi);
                $(".gdPrice").text(e.price);
                $(".miaomi").text(e.mi);
                $(".nowNum").text(e.qty - e.sold);
                
                
				
                // 判断当前库存是否满足最小快速选择
                if (e.qty - e.sold < 3) {
                    $(".fastnum").val(e.qty - e.sold);
                    $(".f_nums").removeClass("f_numsON");
                } else {
                    $(".fastnum").val(3);
                }
				

                if (getCookie("cart_count")) {
                    if (getCookie("cart_count") > 0) {
                        $("#cart_count").html("<sup>" + getCookie("cart_count") + "</sup>")
                    }
                }
                a();
                //给轮播图高度(重置 2019年4月15日 20:57:44)
                $('.goods-detail-top').css('height', $(window).width());
                $('.goods-detail-pic ul li').css('height', $(window).width());
                //详情图懒加载
                $(window).scroll(function() {
                    if ($("img.lazyload").length == 0) {
                        return;
                    }
                    if ($('.goods-body .more.wait').length > 0 && $('.goods-body').height() >= 600) {
                        $('.goods-body .more').show().removeClass('wait');
                    }
                    if (($(window).scrollTop() + $(window).height()) > $("img.lazyload").eq(0).offset().top) {
                        $("img.lazyload").eq(0).attr('src', $("img.lazyload").eq(0).attr('data-original')).removeClass('lazyload').removeAttr('data-original');
                    }
                })

                $('.goods-body .more').click(function() {
                    $('.goods-body').css('max-height', 'initial');
                    $('.goods-body .more').remove();
                });

                $(".pddcp-arrow").click(function() {
                    $(this).parents(".pddcp-one-wp").toggleClass("current")
                });

                //立刻购买事件
                $(".buy-now").click(function() {
                	var e = getCookie("key");
                    //判断当前是否可以下单
                    if ($(this).hasClass("no")) {
                        if (!e) {
                            layer.open({
                                content: "请先登录~",
                                btn: '确定',
                                shadeClose: false,
                                yes: function() {
                                    window.location.href = WapSiteUrl + "/member/login.html";
                                }
                            });
                        }else if(parseFloat($("#good_miaomi").text()) > parseFloat($("#my_miaomi").text())){
                        	layer.open({ content: "抱歉，当前用户秒米不足所选商品", skin: 'msg', time: 2 });
                        	return false;
                        }
					}else{	
						//获取用户信息
                        var o = {};
                        o.key = e;
                        o.cart_id = r + "|" + $(".fastnum").val();
                        o.address_id = address;
                        o.pay_name = "online";
                        o.goods_type = "40";
                        o.invoice_id = "";
                        o.voucher = '';
                        o.is_goodsfcode = 1;
                        o.shop_to = 0;
						o.job_id = job_id;
                        $.ajax({
                            type: "post",
                            url: ApiUrl + "/Memberbuy/buy_step2.html",
                            data: o,
                            dataType: "json",
                            success: function(e) {
                                console.log(e)
                                if (e.code != 10000) {
                                    layer.open({ content: e.message, skin: 'msg', time: 2 });
                                } else {
                                    //带参，跳转到收银台页面
                                    $("#onlineTotal").text($("#good_price").text());
									$("#miaomi").text($("#good_miaomi").text());
                                    $(".alt_pay").show();
                                    pay_sn = e.result.pay_sn;
									payment_code = e.result.payment_code;
                                    //location.href = WapSiteUrl + "/order/buy_step1.html?goods_id=" + r + "&buynum=" + t;
//                                  toPay(e.result.pay_sn, "memberbuy", "pay");
                                }
                            }
                        })
                    }
                })

					
                //秒杀数量+使用方式点击事件
                $(".f_nums").each(function(i) {
                    $(".f_nums").eq(i).on("click", function() {
                        $(this).addClass("f_numsON").siblings().removeClass("f_numsON");
                        var choseNum = $(this).attr("value");
                        $(".fastnum").val(choseNum);
                        initNew();
                    });
                });
                
                //快速选择事件
                $(".fast_types").each(function(i) {
                    $(".fast_types").eq(i).on("click", function() {
                    	console.log(address);
                        $(this).addClass("fast_typeON").siblings().removeClass("fast_typeON");
                        //未登录
                        if(!getCookie("key")){
                        	$(".getUads").show();
                        	$(".getUadsNo").show();
                        	$(".getUads_infos").hide();
                        }else{
	                        if (i == 0) {   //自用
	                            $(".getUads").hide();
	                        } else {
	                            $(".getUads").show();
	                            //判断当前用户是否存在地址
	                            if (!address) {
	                                $(".getUadsNo").show();
	                                $(".getUadsNo").text("选择地址");
	                                $(".getUads_infos").hide();
	                            } else {
	                                $(".getUadsNo").hide();
	                                $(".getUads_infos").show();
	                            }
	                        }
                        }
                    });
                });
                
				//编辑用户选择地址功能
				$(".getUadsNo").on("click",function(){
					window.location.href = WapSiteUrl + "/member/address_list.html?fromTo=spike&goods_id="+goods_id;
				});
				$(".other_ads").on("click",function(){
					window.location.href = WapSiteUrl + "/member/address_list.html?fromTo=spike&goods_id="+goods_id;
				});
				
				
				
                
                //增加&&减少
                $(".f_numAdd").on("click", function() {
                    $(".f_nums").removeClass("f_numsON");
                    var newNums = $(".fastnum").val();
                    newNums++;
                    if (newNums > 99) {
                        return false;
                    }
                    $(".fastnum").val(newNums);
                    initNew();
                });
                $(".f_numCut").on("click", function() {
                    $(".f_nums").removeClass("f_numsON");
                    var newNums = $(".fastnum").val();
                    newNums--;
                    if (newNums < 1) {
                        return false;
                    }
                    $(".fastnum").val(newNums);
                    initNew();
                });



                $("#buynum").blur(n);
                $.animationUp({
                    valve: ".animation-up,#goods_spec_selected",
                    wrapper: "#product_detail_spec_html",
                    scroll: "#product_roll",
                    start: function() {
                        $("#product_detail_foot_html").css('z-index', '30')
                    },
                    close: function() {
                        $("#product_detail_foot_html").css('z-index', '1')
                        $("#product_detail_foot_html .pintuan-now").attr('fieldid', '0')
                        $("#product_detail_foot_html .pintuan-now").text(ds_lang.immediately_open_regiment);
                    }
                });
                $.animationUp({
                    valve: "#getVoucher",
                    wrapper: "#voucher_html",
                    scroll: "#voucher_roll"
                });
                $("#voucher_html").on("click", ".btn", function() {
                    getFreeVoucher($(this).attr("data-tid"))
                });

				// 页面初始化
                initNew();
                
				//支付面板事件
				$(".alt_pay_tip").on("click",function(){
					$(".alt_pay").hide();
				});
				$(".dstouch-bottom-mask-close").on("click",function(){
					$(".alt_pay").hide();
				});
				
				$("#toPay").on("click",function(){
					location.href = http+SiteDomain + "/?s=mobile/Memberpayment/commonPay/key/" + getCookie("key") + "/pay_sn/" + pay_sn + "/payment_code/" + payment_code;
				});
				
            }
        });
    }

    //这里修改成用户创建新地址
//  $(".getUadsNo").on("click", function() {
//      console.log(e);
//      $.areaSelected({
//          success: function(e) {
//              //默认已经选择好了地址
//              console.log(e);
//              $(".getUads_infos").empty();
//              $(".getUadsNo").hide();
//              $(".getUads_infos").show();
//              $(".getUads_infos").append('<div class="u_infos"><span style="margin: 0 0.2rem;" class="uname">'+uname+'</span><span style="margin: 0 0.2rem;" class="uphone">'+uphone+'</span></div>');
//              $(".getUads_infos").append('<div class="addre">' + e.area_info + '</div>');
//              $(".getUads_infos").append('<div class="addre"><input type="text" class="newAdd" placeholder="请输入详细地址" /><span class="add_btns">确定</span></div>');
//              //              
//          }
//      })
//  });

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

function initNew() {
    var good_price = parseFloat($("#good_price").text());
    var good_miaomi = parseFloat($("#good_miaomi").text());
    var good_nums = parseFloat($(".fastnum").val());
    var good_goto = $(".fast_typeON").text();
    //赋值
    $(".order_show_infos .gdNums").text(good_nums);
    $(".order_show_infos .gdType").text(good_goto);
    $(".order_show_infos .gdPrice").text(good_price * good_nums);
    $(".order_show_infos .gdMiaomi").text(good_miaomi * good_nums);
    //判断秒米是否支持本次活动
    if(parseFloat($("#good_miaomi").text()) > miaomi){
    	$(".buy-now").addClass("no");
    }else{
    	$(".buy-now").removeClass("no");
    }
    //特殊处理，自用商品
    if(fromTo == "spike"){
    	$(".fast_types").removeClass("fast_typeON");
    	$(".fast_types").eq(1).addClass("fast_typeON");
    	$(".getUads").show();
    	$(".uname").text(sessionStorage.getItem("spike_userName"));
    	$(".uphone").text(sessionStorage.getItem("spike_userphone"));
    	$(".show_address").text(sessionStorage.getItem("spike_address_info"));
    }
}