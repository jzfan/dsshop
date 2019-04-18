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
                if(a.result.inviter_open == 1){
                    $('#inviter').show();
                }
                $('.userinfo .u-img').html('<img src="'+a.result.member_info.member_avatar+'"><i>'+a.result.member_info.level_name+'</i>');
                $('.userinfo .u-accounts').html('<span>'+a.result.member_info.member_truename+'</span>');
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
                //判断用户，是否是TP||BD会员
                // 1 如果用户，什么都不是，那么，就显示成为2种会员
                // 2 如果是TP，那就显示成为BD
                // 3 如果是BD  那就显示，会员卡包
                var vip_tp =a.result.member_info.is_tp;
                var vip_bd =a.result.member_info.is_bd;
                console.log(vip_tp);
                console.log(vip_bd);
                $(".vips").hide();
                if(vip_tp == 0 && vip_bd == 0){
                    $(".vips").eq(0).show();
                    $(".vips").eq(1).show();
                    $(".vips").eq(3).show();
                }else if(vip_tp != 0 && vip_bd == 0){
                    $(".vips").eq(1).show();
                }else if(vip_tp != 0 && vip_bd != 0){
                    $(".vips").eq(2).show();
                }
                
                getConfig();
                
                return false;
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
    
    //获取后台配置字段&&保存本地
    function getConfig(){
		var langConfs;
		var e = getCookie("key");
		$.ajax({
		    url: ApiUrl+"/api/getconfig",
		    type: 'post',
		    dataType: 'json',
		    data:{key:e,host:"127.0.0.1"},
		    success: function(res) {
		    	langConfs=res.result;
		    	setCookie("user_config",JSON.stringify(langConfs));
		    	var getCookUser = getCookie("user_config");
		    },
		    error:function(e){
		    	layer.open({content: '获取失败~',skin: 'msg',time: 2});
				return false;
		    }
		});
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