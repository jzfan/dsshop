var local;
var map;
var key = getCookie("key");
var goods_type = getQueryString("goods_type");
var ifcart = getQueryString("ifcart");
var pintuangroup_id = getQueryString("pintuangroup_id");
var pintuan_id = getQueryString("pintuan_id");
var offline_yh=0;
var order_amount=0;  //本次支付付款总额
if (ifcart == 1) {
    var cart_id = getQueryString("cart_id");
} else {
    var cart_id = getQueryString("goods_id") + "|" + getQueryString("buynum")
}
var pay_name = "online";
var invoice_id = 0;
var address_id, vat_hash, offpay_hash, offpay_hash_batch, voucher, pd_pay, password, fcode = "",
rcb_pay, payment_code;
var message = {};
var freight_hash, city_id, area_id;
var area_info;
var goods_id;
$(function() {
    //页面进入，加载百度地图脚本
    $.ajax({
        url: ApiUrl + "/Index/get_baidu_ak.html",
        async:false,
        dataType: "json",
        success: function(e) {
            loadScript("https://api.map.baidu.com/getscript?v=2.0&ak="+e.result.baidu_ak+"&services=&t=",function(){
                init_map();
            });
        }
    });

	//收货地址点击交互
    $("#list-address-valve").click(function() {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberaddress/address_list.html",
            data: {
                key: key
            },
            dataType: "json",
            async: false,
            success: function(e) {
                checkLogin(e.login);
                if (e.result.address_list == null) {
                    return false
                }
                var a = e.result;
                a.address_id = address_id;
                var i = template("list-address-add-list-script", a);
                $("#list-address-add-list-ul").html(i);
            }
        })
    });
    $.animationLeft({
        valve: "#list-address-valve",
        wrapper: "#list-address-wrapper",
        scroll: "#list-address-scroll"
    });

    //收货地址面板，点击选择地址事件
    $("#list-address-add-list-ul").on("click", "li",function() {
        $(this).addClass("selected").siblings().removeClass("selected");
        eval("address_info = " + $(this).attr("data-param"));
        _init(address_info.address_id);
        $("#list-address-wrapper").find(".header-l > a").click();
    });
    $.animationLeft({
        valve: "#new-address-valve",
        wrapper: "#new-address-wrapper",
        scroll: ""
    });
    $.animationLeft({
        valve: "#select-payment-valve",
        wrapper: "#select-payment-wrapper",
        scroll: ""
    });

    //新增地址面板中，--》选择地址点击事件
    $("#new-address-wrapper").on("click", "#varea_info",function() {
        $.areaSelected({
            success: function(e) {
                city_id = e.area_id_2 == 0 ? e.area_id_1: e.area_id_2;
                area_id = e.area_id;
                area_info = e.area_info;
                $("#varea_info").val(e.area_info)
                change_map(e.area_info);
            }
        });
    });

    //新增地址面板中，--》定位功能点击事件
    $(".public-pos").on("click", function() {
        $.addressSelected({success: function(a) {
                $('#latitude').val(a.lat);
                $('#longitude').val(a.lng);
                $('#vaddress').val(a.address);
            }})
    });
    $.animationLeft({
        valve: "#invoice-valve",
        wrapper: "#invoice-wrapper",
        scroll: ""
    });

    template.helper("isEmpty",function(e) {
        var a = true;
        $.each(e,
        function(e, i) {
            a = false;
            return false;
        });
        return a;
    });
    template.helper("pf",function(e) {
        return parseFloat(e) || 0
    });
    template.helper("p2f",function(e) {
        return (parseFloat(e) || 0).toFixed(2)
    });

    //页面初始化脚本,获取用户登录信息&&购物信息
    var _init = function(e) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberbuy/buy_step1.html",
            dataType: "json",
            data: {
                key: key,
                cart_id: cart_id,
                ifcart: ifcart,
                address_id: e,
                goods_type:goods_type,
                pintuangroup_id: pintuangroup_id,
                pintuan_id: pintuan_id,
            },
            success: function(e) {
                //获取线下支付优惠率存入页面  && 本次支付总额
                offline_yh = e.result.offline;
                $(".tit2 em").text(offline_yh);
                order_amount =e.result.order_amount;

                if (e.code != 10000) {
                    layer.open({content: e.message, btn: '我知道了'});
                    return false
                }
                e.result.WapSiteUrl = WapSiteUrl;
                var i = template("goods_list", e.result);
                $("#deposit").html(i);
                if (fcode == "") {
                        var a = e.result.cart_list.goods_list.is_goodsfcode;
                        if (a == "1") {
                            $("#container-fcode").removeClass("hide");
                            goods_id = e.result.cart_list[t].goods_list[0].goods_id
                        }
                }
                $("#container-fcode").find(".submit").click(function() {
                    fcode = $("#fcode").val();
                    if (fcode == "") {
                        layer.open({content: '请填写F码',skin: 'msg',time: 2});
                        return false
                    }
                    $.ajax({
                        type: "post",
                        url: ApiUrl + "/Memberbuy/check_fcode.html",
                        dataType: "json",
                        data: {
                            key: key,
                            goods_id: goods_id,
                            fcode: fcode
                        },
                        success: function(e) {
                            if (e.code != 10000) {
                                layer.open({content: e.message,skin: 'msg',time: 2});
                                return false
                            }
                            layer.open({content: '验证成功',skin: 'msg',time: 2});
                            $("#container-fcode").addClass("hide")
                        }
                    })
                });
                if ($.isEmptyObject(e.result.address_info)) {
                    layer.open({
                        content: '请添加地址？'
                        , btn: ['确定', '取消']
                        , yes: function (index) {
                            $("#new-address-valve").click();
                            layer.close(index);
                        }
                        , no: function (index) {
                            history.go( - 1)
                            layer.close(index);
                        }
                    });
                    return false
                }
                if (typeof e.result.inv_info.invoice_id != "undefined") {
                    invoice_id = e.result.inv_info.invoice_id
                }
                $("#invContent").html(e.result.inv_info.content);
                vat_hash = e.result.vat_hash;
                freight_hash = e.result.freight_hash;
                insertHtmlAddress(e.result.address_info, e.result.address_api);
                voucher = "";
                voucher_temp = [];
                for (var t in e.result.cart_list) {
                    voucher_temp.push([e.result.cart_list.voucher_info.vouchertemplate_id + "|" + t + "|" + e.result.cart_list.voucher_info.voucher_price])
                }
                voucher = voucher_temp.join(",");
                    $("#Total").html(e.result.final_total_list);
                    var a = 0;
                    a += parseFloat(e.result.final_total_list);
                    message = "";
                    $("#Message" ).on("change",function() {
                        message = $(this).val()
                    })
                var r = a;
                if (r <= 0) {
                    r = 0
                }
                //获取初始化页面数据--需要支付的金额
                $("#totalPrice,#onlineTotal").html(r.toFixed(2))
            }
        })
    };

    rcb_pay = 0;
    pd_pay = 0;
    _init();

    var insertHtmlAddress = function(e, a) {
        address_id = e.address_id;
        $("#true_name").html(e.address_realname);
        $("#mob_phone").html(e.address_mob_phone);
        $("#address").html(e.area_info + e.address_detail);
        area_id = e.area_id;
        city_id = e.city_id;
        if (a.content) {
            for (var i in a.content) {
                $("#storeFreight" + i).html(parseFloat(a.content[i]).toFixed(2))
            }
        }
        offpay_hash = a.offpay_hash;
        offpay_hash_batch = a.offpay_hash_batch;
        if (a.allow_offpay == 1) {
            $("#payment-offline").show()
        }
        if (!$.isEmptyObject(a.no_send_tpl_ids)) {
            $("#ToBuyStep2").parent().removeClass("ok");
            for (var t = 0; t < a.no_send_tpl_ids.length; t++) {
                $(".transportId" + a.no_send_tpl_ids[t]).show()
            }
        } else {
            $("#ToBuyStep2").parent().addClass("ok")
        }
    };

    //在线支付事件
    $("#payment-online").click(function() {
        pay_name = "online";
        $("#select-payment-wrapper").find(".header-l > a").click();
        $("#select-payment-valve").find(".current-con").html("在线支付");
        $(this).addClass("sel").siblings().removeClass("sel");
        getAllPay();
        pay_offline();
    });

    //线下支付事件
    $("#payment-offline").click(function() {
        pay_name = "offline";
        //$("#select-payment-wrapper").find(".header-l > a").click();
        $(this).addClass("sel").siblings().removeClass("sel");
        pay_offline();
    });

    //线下支付填写信息面板
    function pay_offline(){
        if($("#payment-offline").hasClass("sel")){
            $(".pay_offline_info").show();
        }else{
            $(".pay_offline_info").hide();
        }
    };

    //xxPay_pic 支付二维码方法事件
    $(".xxPay_pic img").on("click",function(){
       $(".showBigPay").show();
    });
    $(".show_close").on("click",function(){
        $(".showBigPay").hide();
    });

    //上传支付凭证确定事件
    $('input[name="refund_pic"]').ajaxUploadImage({
        url: ApiUrl + "/Memberrefund/upload_pic.html",
        data: {
            key: key
        },
        start: function(e) {
            e.parent().after('<div class="upload-loading"><i></i></div>');
            e.parent().siblings(".pic-thumb").remove()
        },
        success: function(e, a) {
            checkLogin(a.login);
            if (a.code != 10000) {
                e.parent().siblings(".upload-loading").remove();
                layer.open({content: a.message,skin: 'msg',time: 2});
                return false
            }
            e.parent().after('<div class="pic-thumb"><img src="' + a.result.pic + '"/></div>');
            e.parent().siblings(".upload-loading").remove();
            //$('input[name="offline_pic"]').val(a.result.file_name);
            e.parents("a").next().val(a.result.file_name)
        }
    });

    //重新计算支付金额方法
    function getAllPay(){
        $("#totalPrice,#onlineTotal").html(order_amount);
    }

    //线下支付,确定线下支付事件
    $(".pay_off_btn").on("click",function(){
        var pay_pic = $('input[name="refund_pic[0]"]').val();
        if(!pay_pic){
            layer.open({content: '请上传支付凭证！',skin: 'msg',time: 2});
            return false;
        }else{
            //添加判断，如果是线下支付，重新计算支付金额
            $("#select-payment-valve").find(".current-con").html("线下支付");
            var offline_payM = order_amount;
            var new_offline = offline_payM - parseFloat(offline_payM * parseFloat(offline_yh) / 100);
            console.log(new_offline);
            $("#totalPrice,#onlineTotal").html(new_offline.toFixed(2));
            $("#select-payment-wrapper").find(".header-l > a").click();
        }
    });

    //页面校验 -->校验地址信息
    $.sValid.init({
        rules: {
            vtrue_name: "required",
            vmob_phone: "required",
            varea_info: "required",
            vaddress: "required"
        },
        messages: {
            vtrue_name: "姓名必填！",
            vmob_phone: "手机号必填！",
            varea_info: "地区必填！",
            vaddress: "街道必填！"
        },
        callback: function(e, a, i) {
            if (e.length > 0) {
                var t = "";
                $.map(a,function(e, a) {
                    t += "<p>" + e + "</p>"
                });
                layer.open({content: t, skin: 'msg', time: 2});
            }
        }
    });

    //新增收货地址面板  保存事件
    $("#add_address_form").find(".btn").click(function() {
        if ($.sValid()) {
            var e = {};
            e.key = key;
            e.true_name = $("#vtrue_name").val();
            e.mob_phone = $("#vmob_phone").val();
            e.address = $("#vaddress").val();
            e.longitude = $("#longitude").val();
            e.latitude = $("#latitude").val();
            e.city_id = city_id;
            e.area_id = area_id;
            e.area_info = $("#varea_info").val();
            e.is_default = 0;
            $.ajax({
                type: "post",
                url: ApiUrl + "/Memberaddress/address_add.html",
                data: e,
                dataType: "json",
                success: function(a) {
                    if (a.code == 10000) {
                        e.address_id = a.result.address_id;
                        _init(e.address_id);
                        $("#new-address-wrapper,#list-address-wrapper").find(".header-l > a").click()
                    }
                }
            })
        }
    });

    //发票面板 --》不需要发票点击事件
    $("#invoice-noneed").click(function() {
        $(this).addClass("sel").siblings().removeClass("sel");
        $("#invoice_add,#invoice-list").hide();
        invoice_id = 0
    });

    //发票面板 --》需要发票点击事件 && 校验用户是否合法
    $("#invoice-need").click(function() {
        $(this).addClass("sel").siblings().removeClass("sel");
        $("#invoice-list").show();

        //获取发票明细数据
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberinvoice/invoice_content_list.html",
            data: {
                key: key
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                var a = e.result;
                var i = "";
                $.each(a.invoice_content_list,function(e, a) {
                    i += '<option value="' + a + '">' + a + "</option>"
                });
                $("#inc_content").append(i);
                console.log(i);
            }
        });
        //获取发票明细数据
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberinvoice/invoice_list.html",
            data: {
                key: key
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                var a = template("invoice-list-script", e.result);
                $("#invoice-list").html(a);
                if (e.result.invoice_list.length > 0) {
                    //默认获取第一张
                    invoice_id = e.result.invoice_list[0].invoice_id;
                    $("#invoice-list label").eq(0).addClass("checked");
                }
                //删除发票类型
                $(".del-invoice").click(function() {
                    var e = $(this);
                    var a = $(this).attr("invoice_id");
                    $.ajax({
                        type: "post",
                        url: ApiUrl + "/Memberinvoice/invoice_del.html",
                        data: {
                            key: key,
                            invoice_id: a
                        },
                        success: function(a) {
                            if (a) {
                                e.parents("label").remove()
                            }
                            return false
                        }
                    })
                })
            }
        })
    });

    $('input[name="invoice_title_select"]').click(function() {
        if ($(this).val() == "person") {
            $("#inv-title-li").hide();
            $("#inv-code-li").hide();
        } else {
            $("#inv-title-li").show();
            $("#inv-code-li").show();
        }
    });

    //新增发票类型事件
    $("#invoice-div").on("click", "#invoiceNew",function() {
        invoice_id = 0;
        $("#invoice_add,#invoice-list").show()
    });

    //选择发票 -->获取发票ID
    $("#invoice-list").on("click", "label",function() {
        invoice_id = $(this).find("input").val();
    });

    //发票面板，点击确认事件
    $("#invoice-div").find(".btn-l").click(function() {
        if ($("#invoice-need").hasClass("sel")) {
            //如果不存在发票id，则去添加该发票，并且重新获取invoice_id；
            //0表示去新增加发票id，非0则是选择的发票id；
            if (invoice_id == 0) {
                var e = {};
                e.key = key;
                e.invoice_title_select = $('input[name="invoice_title_select"]:checked').val();
                e.invoice_title = $("input[name=invoice_title]").val();
                e.invoice_code = $("input[name=invoice_code]").val();
                e.invoice_content = $("select[name=invoice_content]").val();
                $.ajax({
                    type: "post",
                    url: ApiUrl + "/Memberinvoice/invoice_add.html",
                    data: e,
                    dataType: "json",
                    success: function(e) {
                        if (e.result.invoice_id > 0) {
                            invoice_id = e.result.invoice_id;
                        }
                    }
                });
                $("#invContent").html(e.invoice_title + " " + e.invoice_code + " " + e.invoice_content)
            } else {
                //获取发票内容
                $("#invContent").html($("#inv_" + invoice_id).html());
            }
        } else {
            $("#invContent").html("不需要发票")
        }
        //模拟点击事件，返回当前面板
        $("#invoice-wrapper").find(".header-l > a").click()
    });

    //提交订单事件
    $("#ToBuyStep2").click(function() {
        var e = "";
        //拆开留言信息(未知因素)
        for (var a in message) {
            e += a + "|" + message[a] + ",";
        }
        if(pay_name == "offline"){
            pay_offline_pic = $('input[name="refund_pic[0]"]').val();
            if(pay_offline_pic == ""){
                layer.open({content: '未获取到支付凭证，请重新上传!',skin: 'msg',time: 2});
                return false;
            }
        }else{
            pay_offline_pic = "";
        }
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberbuy/buy_step2.html",
            data: {
                key: key,
                ifcart: ifcart,
                cart_id: cart_id,
                address_id: address_id,
                vat_hash: vat_hash,
                offpay_hash: offpay_hash,
                offpay_hash_batch: offpay_hash_batch,
                pay_name: pay_name,
                invoice_id: invoice_id,
                voucher: voucher,
                pd_pay: pd_pay,
                goods_type: goods_type,
                password: password,
                fcode: fcode,
                rcb_pay: rcb_pay,
                pay_message: e,
                pintuangroup_id: pintuangroup_id,
                pintuan_id: pintuan_id,
                pay_offline_pic:pay_offline_pic,
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                if (e.code != 10000) {
                    layer.open({content: e.message,skin: 'msg',time: 2});
                    return false
                }
                if (e.result.payment_code == "offline") {
                    window.location.href = WapSiteUrl + "/member/order_list.html"
                } else {
                    delCookie("cart_count");
                    toPay(e.result.pay_sn, "memberbuy", "pay");
                }
            }
        })
    })
});

//画地图
function init_map(){
    map = new BMap.Map('mymap');
    var lng=$('#longitude').val();
    var lat=$('#latitude').val();
    if(lng=='' && lat==''){
    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function (r) {
        if (this.getStatus() == BMAP_STATUS_SUCCESS) {
            var lng = r.point.lng;
            var lat = r.point.lat;
            var point = new BMap.Point(lng, lat);
            map.centerAndZoom(point, 16);
            document.getElementById("longitude").value = lng;
            document.getElementById("latitude").value = lat;

        } else {
            alert('failed' + this.getStatus());
        }
    }, {enableHighAccuracy: true})
    }else{
        var point = new BMap.Point(lng, lat);
        map.centerAndZoom(point, 16);
    }
    var options = {
        onSearchComplete: function (results) {
            // 判断状态是否正确
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                if (results.getCurrentNumPois() > 0) {
                    document.getElementById("longitude").value = results.getPoi(0).point.lng;
                    document.getElementById("latitude").value = results.getPoi(0).point.lat;
                    setCookie('longitude', results.getPoi(0).point.lng, 30);
                    setCookie('latitude', results.getPoi(0).point.lat, 30);
                }
            }
        }
    };
    local = new BMap.LocalSearch(map, options);
}

//选择地图
function change_map(name){
    if(name!=''){
        map.centerAndZoom(name,16);
        map.setCurrentCity(name);
        local.search(name);
//        var point=map.getCenter();
//        document.getElementById("longitude").value = point.lng;
//        document.getElementById("latitude").value = point.lat;
    }
    
}