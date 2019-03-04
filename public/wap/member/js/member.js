$(function() {
    if (getQueryString("key") != "") {
        var a = getQueryString("key");
        var e = getQueryString("username");
        addCookie("key", a);
        addCookie("username", e)
    } else {
        var a = getCookie("key")
    }
    if (a) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/Member/index.html",
            data: {
                key: a
            },
            dataType: "json",
            success: function(a) {
                checkLogin(a.result.login);
                if(a.result.inviter_open){
                    $('#inviter').show();
                }
                $('.userinfo .u-img').html('<img src="'+a.result.member_info.member_avatar+'"><i>'+a.result.member_info.level_name+'</i>');
                $('.userinfo .u-accounts').html('<span>'+a.result.member_info.member_name+'</span>');
                $('.m-property .points .m-num em').text(a.result.member_info.member_points);
                $('.m-property .voucher .m-num em').text(a.result.member_info.voucher_count);
                $('.m-property .predeposit .m-num em').text(a.result.member_info.available_predeposit);

                if(a.result.member_info.order_nopay_count > 0){
                    $('#order_ul li').eq(0).find('a').prepend('<em></em>');
                }
                if(a.result.member_info.order_noship_count > 0){
                    $('#order_ul li').eq(1).find('a').prepend('<em></em>');
                }
                if(a.result.member_info.order_noreceipt_count > 0){
                    $('#order_ul li').eq(2).find('a').prepend('<em></em>');
                }
                if(a.result.member_info.order_noeval_count > 0){
                    $('#order_ul li').eq(3).find('a').prepend('<em></em>');
                }
                if(a.result.member_info.order_refund_count > 0){
                    $('#order_ul li').eq(4).find('a').prepend('<em></em>');
                }
    
                return false
            }
        })
    } else {
        $('.userinfo .u-img').html('<img src="'+http+SiteDomain+'/uploads/home/common/default_user_portrait.gif">');
        $('.userinfo .u-accounts').html('<a href="login.html">登录 / 注册<i class="arrow iconfont">&#xe638;</i></a>');
       
        return false
    }
    $.scrollTransparent()
    
    //用户中心判断是否登录，登录则显示注销
    var a = getCookie("key");
    if (a) {
        var e = '<a id="logoutbtn" href="javascript:void(0);" class="btn">注销</a>';
        $(".member-logout").html(e);
    }
    
    $("#logoutbtn").click(function() {
        var a = getCookie("username");
        var e = getCookie("key");
        var i = "wap";
        $.ajax({
            type: "post",
			dataType: "json",
            url: ApiUrl + "/Logout/index.html",
            data: {
                username: a,
                key: e,
                client: i
            },
            success: function (a) {
                if (a.code == 10000) {
                    delCookie("username");
                    delCookie("key");
                    location.href = WapSiteUrl
                } else {
                    alert(a.message);
                }
            }
        })
    })
    
});