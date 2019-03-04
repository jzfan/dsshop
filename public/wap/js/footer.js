$(function() {
    var a = getCookie("key");
    str = '<div class="footer-blank"></div>'
    str += '<div class="footer-nav"><ul>';
    str += '<li class="current"><a href="' + WapSiteUrl + '/index.html"><i class="iconfont">&#xe751;</i><span>首页</span></a></li>';
    str += '<li><a href="' + WapSiteUrl + '/mall/product_first_categroy.html"><i class="iconfont">&#xe754;</i><span>分类</span></a></li>';
    str += '<li><a href="' + WapSiteUrl + '/mall/cart_list.html"><i class="iconfont">&#xe73e;</i><span>购物车</span></a></li>';
    if (a) {
        str += '<li><a href="' + WapSiteUrl + '/member/member.html"><i class="iconfont">&#xe75e;</i><span>我的</span></a></li></ul></div>';
    } else {
        str += '<li><a href="' + WapSiteUrl + '/member/login.html"><i class="iconfont">&#xe75e;</i><span>我的</span></a></li></ul></div>';
    }
    $("#footer").html(str);
    if(Domain.indexOf(WapSiteUrl + "/index.html")>-1){
        $('.footer-nav li').eq(0).addClass('active');
        $('.footer-nav li').eq(0).find('.iconfont').html('&#xe750;');
    }
    if(Domain.indexOf(WapSiteUrl + "/mall/product_first_categroy.html")>-1){
        $('.footer-nav li').eq(1).addClass('active');
        $('.footer-nav li').eq(1).find('.iconfont').html('&#xe753;');
    }
    if(Domain.indexOf(WapSiteUrl + "/mall/cart_list.html")>-1){
        $('.footer-nav li').eq(2).addClass('active');
        $('.footer-nav li').eq(2).find('.iconfont').html('&#xe73d;');
    }
    if(Domain.indexOf(WapSiteUrl + "/member/member.html")>-1){
        $('.footer-nav li').eq(3).addClass('active');
        $('.footer-nav li').eq(3).find('.iconfont').html('&#xe75d;');
    }
});
if(WeiXinOauth){
    var key = getCookie('key');
    if(key==null){
        var ua = window.navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){
//            window.location.href=ApiUrl+"/Wxauto/login.html?ref="+encodeURIComponent(window.location.href);
            
            $.getJSON(ApiUrl+"/Wxauto/login.html?ref="+encodeURIComponent(window.location.href), function (wxauto_login) {
                if (wxauto_login.code == "10000" ) {
                    window.location.href=wxauto_login.result;
                }else{
                    layer.open({content: wxauto_login.message, skin: 'msg', time: 2});
                }
            });
        }
    }
}