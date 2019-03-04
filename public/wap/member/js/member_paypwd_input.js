$(function() {
    var e = getCookie("key");
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    loadSeccode();
    $("#refreshcode").bind("click",
            function() {
                loadSeccode()
            });
    $.ajax({
        type: "get",
        url: ApiUrl + "/Memberaccount/get_paypwd_info.html",
        data: {
            key: e
        },
        dataType: "json",
        success: function(e) {
            if (e.code == 10000) {
                if (!e.result.state) {
                    layer.open({content: '请先设置支付密码', skin: 'msg', time: 2});
                    setTimeout("location.href = WapSiteUrl+'/member/member_paypwd_step1.html'", 2e3)
                }
            }
        }
    });
    $.sValid.init({
        rules: {
            password: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            captcha: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            password: {
                required: "请填写支付密码",
                minlength: "请正确填写支付密码",
                maxlength: "请正确填写支付密码"
            },
            captcha: {
                required: "请填写图形验证码",
                minlength: "图形验证码不正确"
            }
        },
        callback: function (e, a, t) {
            if (e.length > 0) {
                var r = "";
                $.map(a,
                        function (e, a) {
                            r += "<p>" + e + "</p>"
                        });
                layer.open({content: r, skin: 'msg', time: 2});
            }
        }
    });
    $("#nextform").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        if ($.sValid()) {
            var a = $.trim($("#password").val());
            var t = $.trim($("#captcha").val());
            var r = $.trim($("#codekey").val());
            $.ajax({
                type: "post",
                url: ApiUrl + "/Memberaccount/check_paypwd.html",
                data: {
                    key: e,
                    password: a,
                    captcha: t,
                    codekey: r
                },
                dataType: "json",
                success: function(e) {
                    if (e.code == 10000) {
                        location.href = WapSiteUrl + "/member/member_mobile_bind.html"
                    } else {
                        layer.open({content: e.message, skin: 'msg', time: 2});
                        $("#codeimage").attr("src", ApiUrl + "/Seccode/makecode.html?k=" + $("#codekey").val() + "&t=" + Math.random());
                        $("#captcha").val("")
                    }
                }
            })
        }
    })
});