$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    $.ajax({
        type: "get",
        url: ApiUrl + "/Memberaccount/modify_password_step4.html",
        data: {
            key: e
        },
        dataType: "json",
        success: function(e) {
            if (e.code != 10000) {
                layer.open({content: '权限不足或操作超时', skin: 'msg', time: 2});
                setTimeout("location.href = WapSiteUrl+'/member/member_password_step1.html'", 2e3)
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
            password1: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "请填写登录密码",
                minlength: "请正确填写登录密码",
                maxlength: "请正确填写登录密码"
            },
            password1: {
                required: "请填写确认密码",
                equalTo: "两次密码输入不一致"
            }
        },
        callback: function(e, r, a) {
            if (e.length > 0) {
                var s = "";
                $.map(r, function(e, r) {
                    s += "<p>" + e + "</p>"
                });
                layer.open({content: s, skin: 'msg', time: 2});
            }
        }
    });
    $("#nextform").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        if ($.sValid()) {
            var r = $.trim($("#password").val());
            var a = $.trim($("#password1").val());
            $.ajax({
                type: "post",
                url: ApiUrl + "/Memberaccount/modify_password_step5.html",
                data: {
                    key: e,
                    password: r,
                    password1: a
                },
                dataType: "json",
                success: function(e) {
                    if (e.code == 10000) {
                        layer.open({content: '密码修改成功，正在跳转',skin: 'msg',time: 2});
                        setTimeout("location.href = WapSiteUrl+'/member/member_account.html'", 2e3)
                    } else {
                        layer.open({content: e.message, skin: 'msg', time: 2});
                    }
                }
            })
        }
    })
});