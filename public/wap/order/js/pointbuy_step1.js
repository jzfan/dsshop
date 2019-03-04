var key = getCookie("key");
var ifcart = getQueryString("ifcart");
if (ifcart == 1) {
    var cart_id = getQueryString("cart_id")
} else {
    var cart_id = getQueryString("goods_id") + "|" + getQueryString("buynum")
}

var address_id ;
var message = '';
var  city_id, area_id;
var area_info;
var goods_id;
$(function() {
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
                $("#list-address-add-list-ul").html(i)
            }
        })
    });
    $.animationLeft({
        valve: "#list-address-valve",
        wrapper: "#list-address-wrapper",
        scroll: "#list-address-scroll"
    });
    $("#list-address-add-list-ul").on("click", "li",
    function() {
        $(this).addClass("selected").siblings().removeClass("selected");
        eval("address_info = " + $(this).attr("data-param"));
        _init(address_info.address_id);
        $("#list-address-wrapper").find(".header-l > a").click()
    });
    $.animationLeft({
        valve: "#new-address-valve",
        wrapper: "#new-address-wrapper",
        scroll: ""
    });

    $("#new-address-wrapper").on("click", "#varea_info",
    function() {
        $.areaSelected({
            success: function(e) {
                city_id = e.area_id_2 == 0 ? e.area_id_1: e.area_id_2;
                area_id = e.area_id;
                area_info = e.area_info;
                $("#varea_info").val(e.area_info)
            }
        })
    });
    template.helper("isEmpty",
    function(e) {
        var a = true;
        $.each(e,
        function(e, i) {
            a = false;
            return false
        });
        return a
    });
    template.helper("pf",
    function(e) {
        return parseFloat(e) || 0
    });
    template.helper("p2f",
    function(e) {
        return (parseFloat(e) || 0).toFixed(2)
    });
    var _init = function(e) {
        var a = 0;
        $.ajax({
            type: "post",
            url: ApiUrl + "/Pointcart/step1.html",
            dataType: "json",
            data: {
                key: key,
                cart_id: cart_id,
                ifcart: ifcart,
                address_id: e
            },
            success: function(e) {
                checkLogin(e.login);
                if (e.code != 10000) {
                    layer.open({content: e.message,skin: 'msg',time: 2});
                    return false
                }
                e.result.pointprod_arr.WapSiteUrl = WapSiteUrl;
                var i = template("goods_list", e.result.pointprod_arr);
                $("#deposit").html(i);
              
               
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
               
                insertHtmlAddress(e.result.address_info, e.result.address_api);
              
                var r = e.result.pointprod_arr.pgoods_pointall;
                if (r <= 0) {
                    r = 0
                }
                $("#totalPrice").html(r.toFixed(2))
            }
        })
    };

    _init();
    var insertHtmlAddress = function(e, a) {
        address_id = e.address_id;
        $("#true_name").html(e.address_realname);
        $("#mob_phone").html(e.address_mob_phone);
        $("#address").html(e.area_info + e.address_detail);
        area_id = e.area_id;
        city_id = e.city_id;
    
		$("#ToBuyStep2").parent().addClass("ok");
    };

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
                $.map(a,
                function(e, a) {
                    t += "<p>" + e + "</p>"
                });
                layer.open({content: t, skin: 'msg', time: 2});
            }
        }
    });
    $("#add_address_form").find(".btn").click(function() {
        if ($.sValid()) {
            var e = {};
            e.key = key;
            e.true_name = $("#vtrue_name").val();
            e.mob_phone = $("#vmob_phone").val();
            e.address = $("#vaddress").val();
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

    $("#ToBuyStep2").click(function() {
        var e = "";
		e=$('#storeMessage').val();
        
        $.ajax({
            type: "post",
            url: ApiUrl + "/Pointcart/step2.html",
            data: {
                key: key,
                address_options: address_id,               
                pcart_message: e
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                if (e.code != 10000) {
                    layer.open({content: e.message,skin: 'msg',time: 2});
                    return false
                }
				
                alert('兑换成功!');
                window.location.href = WapSiteUrl + "/member/pointorder_list.html";
                                
            }
        });
    });
});