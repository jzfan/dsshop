$(function () {
    var inviter_id = getCookie("inviter_id");
    if(getQueryString("inviter_id")){
        inviter_id = getQueryString("inviter_id");
        //计入cookie
        setCookie('inviter_id', inviter_id, 30);
    }else{
        inviter_id = getCookie("inviter_id");
    }
    if(inviter_id){
        $.getJSON(ApiUrl + "/Login/get_inviter/index.html?inviter_id="+inviter_id, function(e) {
            var t = e.result;
            if(t.member){
            t.WapSiteUrl = WapSiteUrl;
            var r = template("inviter", t);
            $("ul.form-box").prepend(r);
            }

        })  
    }
    loadSeccode();
    $("#refreshcode").bind("click", function () {
        loadSeccode()
    });
    $.sValid.init({
        rules: {usermobile: {required: true, mobile: true}},
        messages: {usermobile: {required: "请填写手机号！", mobile: "手机号码不正确"}},
        callback: function (e, i, r) {
            if (e.length > 0) {
                var l = "";
                $.map(i, function (e, i) {
                    l += "<p>" + e + "</p>"
                });
                layer.open({content: l, skin: 'msg', time: 2});
            }
        }
    });
    $("#refister_mobile_btn").click(
        function () {
            if (!$(this).parent().hasClass("ok")) {
                return false
            }
            var a=$("#captcha").val();
            $.ajax({type: "post", url: ApiUrl + "/Seccode/check.html", data: {captcha: a}, dataType: "json", success: function(e) {
                    if(e.code == 10000) {
                        if ($.sValid()) {
                            setTimeout(location.href = WapSiteUrl+'/member/register_mobile_code.html?mobile='+ $("#usermobile").val(), 1e5);
                        } else {
                            return false
                        }
                    }else {
                        layer.open({content: e.message, skin: 'msg', time: 2});
                        $("#codeimage").attr("src", ApiUrl + "/Seccode/makecode.html");
                        $("#captcha").val("");
                    }
                }});
        })
});