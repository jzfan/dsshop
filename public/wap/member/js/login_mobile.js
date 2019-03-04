
var send_sms_state = 0;  //手机短信验证码发送状态

$(function() {
    var e = getCookie("key");
    if (e) {
        window.location.href = WapSiteUrl + "/member/member.html";
        return;
    }
    var r = document.referrer;
    $.sValid.init({
        rules: {
            usermobile: "required",
            mobilecode: "required"
        },
        messages: {
            usermobile: "手机号必填！",
            mobilecode: "手机验证码必填!"
        },
        callback: function (e, r, a) {
            if (e.length > 0) {
                var i = "";
                $.map(r,
                        function (e, r) {
                            i += "<p>" + e + "</p>"
                        });
                layer.open({content: i, skin: 'msg', time: 2});
            }
        }
    });
    $("#loginbtn").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        var usermobile = $("#usermobile").val();
        var mobilecode = $("#mobilecode").val();
        var client = "wap";
        if ($.sValid()) {
            var chk_captcha = check_sms_captcha(usermobile, mobilecode);
            if (chk_captcha > 0) {
                $.ajax({
                    type: "post",
                    url: ApiUrl + "/Connect/sms_login.html",
                    data: {
                        usermobile: usermobile,
                        mobilecode: mobilecode,
                        client: client
                    },
                    dataType: "json",
                    success: function (e) {
                        if (e.code == 10000) {
                            if (typeof e.result.key == "undefined") {
                                return false
                            } else {
                                updateCookieCart(e.result.key);
                                addCookie("username", e.result.username);
                                addCookie("key", e.result.key);
                                location.href = WapSiteUrl + "/member/member.html";
                            }
                        } else {
                            layer.open({content: e.message, skin: 'msg', time: 2});
                        }
                    }
                })
            } else {
                loadSeccode();
                layer.open({content: '短信验证码校验失败', skin: 'msg', time: 2});
            }
            
        }
    });
    
    //获取短信验证码
    $('#again').click(function(){
        mobile = $('#usermobile').val();        
        if(mobile == '' || ! /^(1{1})+\d{10}$/.test(mobile)){
            layer.open({content: '请输入正确的手机号', skin: 'msg', time: 2});
            return false;
        }
        if(!send_sms_state){
            send_sms_state = 1;
            send_sms(mobile);
        }
    });
    
    $(".weibo").click(function() {
        location.href = ApiUrl + "/Connect/get_sina_oauth2.html"
    });
    $(".qq").click(function() {
        location.href = ApiUrl + "/Connect/get_qq_oauth2.html"
    });
    $(".weixin").click(function() {
        location.href = ApiUrl + "/Login/wxlogin.html"
    })
});


// 发送手机验证码
function send_sms(mobile) {
    $.getJSON(ApiUrl+'/Connect/get_sms_captcha', {type:2,phone:mobile}, function(result){
        if(result.code == 10000){
            layer.open({content: '发送成功',skin: 'msg',time: 2});
            $('.code-again').hide();
            $('.code-countdown').show().find('em').html(result.result.sms_time);
            var times_Countdown = setInterval(function(){
                var em = $('.code-countdown').find('em');
                var t = parseInt(em.html() - 1);
                if (t == 0) {
                    send_sms_state = 0;
                    $('.code-again').show();
                    $('.code-countdown').hide();
                    clearInterval(times_Countdown);
                } else {
                    em.html(t);
                }
            },1000);
        }else{
            send_sms_state = 0;
            loadSeccode();
            layer.open({content: result.message, skin: 'msg', time: 2});
        }
    });
}

//验证手机验证码
function check_sms_captcha(mobile, captcha) {
    var s = 0;
    $.ajax({
        type:'get',
        url:ApiUrl+"/Connect/check_sms_captcha",  
        data:{type:2,phone:mobile,captcha:captcha },
        dataType:'json',
        async:false,
        success:function(result){
            if (result.code == 10000) {
                s = 1;
            }
        }
    });
    return s;
}