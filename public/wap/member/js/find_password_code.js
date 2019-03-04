$(function() {
    var e = getQueryString("mobile");
    $("#usermobile").html(e);
    send_sms(e);
    $("#again").click(function () {
        var a = $("#captcha").val();
        $.ajax({type: "post", url: ApiUrl + "/Seccode/check.html", data: {captcha: a}, dataType: "json", success: function (r) {
                if (r.code == 10000) {
                    send_sms(e);
                } else {
                    layer.open({content: r.message, skin: 'msg', time: 2});
                    $("#codeimage").attr("src", ApiUrl + "/Seccode/makecode.html?t=" + Math.random());
                    $("#captcha").val("");
                }
            }});
    });
    $("#find_password_code").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        var c = $("#mobilecode").val();
        if (c.length == 0) {
            layer.open({content: '请填写验证码', skin: 'msg', time: 2});
        }
        check_sms_captcha(e, c);
        return false;
    });
    loadSeccode();
    $("#refreshcode").bind("click", function() {
        loadSeccode();
    });
});

function send_sms(e) {
    $.getJSON(ApiUrl + "/Connect/get_sms_captcha.html", {
        type: 3,
        phone: e,
    }, function(e) {
        if (e.code == 10000) {
            layer.open({content: '发送成功',skin: 'msg',time: 2});
            $(".code-again").hide();
            $(".code-countdown").show().find("em").html(e.result.sms_time);
            var c = setInterval(function() {
                var e = $(".code-countdown").find("em");
                var a = parseInt(e.html() - 1);
                if (a == 0) {
                    $(".code-again").show();
                    $(".code-countdown").hide();
                    clearInterval(c);
                } else {
                    e.html(a);
                }
            }, 1e3);
        } else {
            loadSeccode();
            layer.open({content: e.message, skin: 'msg', time: 2});
        }
    });
}

function check_sms_captcha(e, c) {
    $.getJSON(ApiUrl + "/Connect/check_sms_captcha.html", {
        type: 3,
        phone: e,
        captcha: c
    }, function(a) {
        if (a.code == 10000) {
            window.location.href = "find_password_password.html?mobile=" + e + "&captcha=" + c
        } else {
            loadSeccode();
            layer.open({content: a.message, skin: 'msg', time: 2});
        }
    })
}