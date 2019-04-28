var key = getCookie("key");
var goods_id = getQueryString("goods_id");
var rcb_pay, pd_pay, payment_code;
function toPay(a, e, p) {
    $.ajax({
        type: "post",
        url: ApiUrl + "/" + e + "/" + p+".html",
        data: {
            key: key,
            pay_sn: a
        },
        dataType: "json",
        success: function(p) {
            checkLogin(p.login);

            console.log(p);
            if (p.code != 10000) {
                layer.open({content: p.message, btn: '我知道了'});
                return false
            }
            $.animationUp({
                valve: "",
                scroll: ""
            });
            $("#onlineTotal").html(p.result.pay_info.pay_amount);
            if (!p.result.pay_info.member_paypwd) {
                $("#wrapperPaymentPassword").find(".input-box-help").show();
            }
            var s = false;
            if (parseFloat(p.result.pay_info.payed_amount) <= 0) {
                if (parseFloat(p.result.pay_info.member_available_pd) == 0 && parseFloat(p.result.pay_info.member_available_rcb) == 0) {
                    $("#internalPay").hide()
                } else {
                    $("#internalPay").show();
                    if (parseFloat(p.result.pay_info.member_available_rcb) != 0) {
                        $("#wrapperUseRCBpay").show();
                        $("#availableRcBalance").html(parseFloat(p.result.pay_info.member_available_rcb).toFixed(2))
                    } else {
                        $("#wrapperUseRCBpay").hide()
                    }
                    if (parseFloat(p.result.pay_info.member_available_pd) != 0) {
                        $("#wrapperUsePDpy").show();
                        $("#availablePredeposit").html(parseFloat(p.result.pay_info.member_available_pd).toFixed(2))
                    } else {
                        $("#wrapperUsePDpy").hide()
                    }
                }
            } else {
                $("#internalPay").hide()
            }
            
            rcb_pay = 0;
            $("#useRCBpay").click(function() {
                if ($(this).prop("checked")) {
                    s = true;
                    $("#wrapperPaymentPassword").show();
                    rcb_pay = 1
                } else {
                    if (pd_pay == 1) {
                        s = true;
                        $("#wrapperPaymentPassword").show()
                    } else {
                        s = false;
                        $("#wrapperPaymentPassword").hide()
                    }
                    rcb_pay = 0
                }
            });
            pd_pay = 0;
            $("#usePDpy").click(function() {
                if ($(this).prop("checked")) {
                    s = true;
                    $("#wrapperPaymentPassword").show();
                    pd_pay = 1
                } else {
                    if (rcb_pay == 1) {
                        s = true;
                        $("#wrapperPaymentPassword").show()
                    } else {
                        s = false;
                        $("#wrapperPaymentPassword").hide()
                    }
                    pd_pay = 0
                }
            });
            payment_code = "";
            if (!$.isEmptyObject(p.result.pay_info.payment_list)) {
                var t = false;
                var r = false;
                var n = navigator.userAgent.match(/MicroMessenger\/(\d+)\./);
                if (parseInt(n && n[1] || 0) >= 5) {
                    t = true
                } else {
                    r = true
                }
                for (var o = 0; o < p.result.pay_info.payment_list.length; o++) {
                    var i = p.result.pay_info.payment_list[o].payment_code;
                    if (i == "offline" && r) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                    $("#" + i).parents("label").show();
                    if (i == "alipay_h5" && r) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                    if (i == "wxpay_jsapi" && t) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                    if (i == "wxpay_h5" && t) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                    if (i == "wxpay_minipro" && t) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                }
            }
            $("#alipay_h5").click(function () {
                payment_code = "alipay_h5";
            });
            $("#wxpay_jsapi").click(function () {
                payment_code = "wxpay_jsapi";
            });
            $("#wxpay_h5").click(function () {
                payment_code = "wxpay_h5";
            });
            $("#wxpay_minipro").click(function () {
                payment_code = "wxpay_minipro";
            });

            if(goods_id){
                //页面滚到最上端
                $("#paymentPassword").on("click",function(){
                    $(window).scrollTop(0);
                });
                $("#paymentPassword").blur(function(){
                    $(window).scrollTop(0);
                });
            }

            //付款点击事件
            $("#toPay").click(function() {
                if (payment_code == "") {
                    layer.open({content: '请选择支付方式',skin: 'msg',time: 2});
                    return false
                }
                if (s) {
                    if ($("#paymentPassword").val() == "") {
                        layer.open({content: '请填写支付密码',skin: 'msg',time: 2});
                        $("#paymentPassword").focus();
                        return false
                    }
                    $.ajax({
                        type: "post",
                        url: ApiUrl + "/Memberbuy/check_pd_pwd.html",
                        dataType: "json",
                        data: {
                            key: key,
                            password: $("#paymentPassword").val()
                        },
                        success: function(p) {
                            if (p.code != 10000) {
                                layer.open({content: p.message,skin: 'msg',time: 2});
                                return false;
                            }
                            goToPayment(a, e == "memberbuy" ? "pay_new": "vr_pay_new")
                        }
                    })
                } else {
                    goToPayment(a, e == "memberbuy" ? "pay_new": "vr_pay_new")
                }
            })
        }
    })
}
function goToPayment(a, e) {
    if(payment_code == "wxpay_minipro"){
        wx.miniProgram.redirectTo({url:"../pay/pay?action="+e+"&key=" + key + "&pay_sn=" + a + "&password=" + $("#paymentPassword").val() + "&rcb_pay=" + rcb_pay + "&pd_pay=" + pd_pay + "&payment_code=" + payment_code});
    }else{
        location.href = http+SiteDomain + "/?s=mobile/Memberpayment/commonpay/key/" + key + "/pay_sn/" + a + "/password/" + $("#paymentPassword").val() + "/rcb_pay/" + rcb_pay + "/pd_pay/" + pd_pay + "/payment_code/" + payment_code;
    }
    
}

/*同调调用可用支付相关显示方式*/
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    var paysn = getQueryString('paysn');
    $.ajax({
        type: 'post',
        url: ApiUrl + "/Memberpayment/payment_list.html",
        data: {key: e},
        dataType: 'json',
        success: function(result) {
            if (result.code == 10000) {
                var data = result.result;
                data.ApiUrl = ApiUrl;
                data.key = e;
                var html = template('pay-sel-tmpl', data);
                $("#pay-sel").html(html);
            }
            else {
                alert(result.message);
                location.href = "member.html?act=member";
            }
        }
    });
});