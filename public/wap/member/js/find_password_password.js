$(function() {
    var e = getQueryString("mobile");
    var a = getQueryString("captcha");
    $("#checkbox").click(function() {
        if ($(this).prop("checked")) {
            $("#password").attr("type", "text")
        } else {
            $("#password").attr("type", "password")
        }
    });
    $.sValid.init({
        rules: {
            password: "required"
        },
        messages: {
            password: "密码必填!"
        },
        callback: function(e, a, r) {
            if (e.length > 0) {
                var s = "";
                $.map(a, function(e, a) {
                    s += "<p>" + e + "</p>"
                });
                layer.open({content: s, skin: 'msg', time: 2});
            }
        }
    });
    $("#completebtn").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        var r = $("#password").val();
        if ($.sValid()) {
            $.ajax({
                type: "post",
                url: ApiUrl + "/Connect/find_password.html",
                data: {
                    phone: e,
                    captcha: a,
                    password: r,
                    client: "wap"
                },
                dataType: "json",
                success: function(e) {
                    if (e.code == 10000) {
                        addCookie("username", e.result.username);
                        addCookie("key", e.result.key);
                        location.href = WapSiteUrl + "/member/member.html"
                    } else {
                        layer.open({content: e.message, skin: 'msg', time: 2});
                    }
                }
            })
        }
    })
});