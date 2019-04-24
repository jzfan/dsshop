var curpage = 1;
var hasmore = true;
var footer = false;
var keyword = decodeURIComponent(getQueryString("keyword"));
var gc_id = getQueryString("gc_id");
var b_id = getQueryString("b_id");
var key = getQueryString("key");
var order = getQueryString("order");
var area_id = getQueryString("area_id");
var price_from = getQueryString("price_from");
var price_to = getQueryString("price_to");
var own_shop = getQueryString("own_shop");
var gift = getQueryString("gift");
var groupbuy = getQueryString("groupbuy");
var xianshi = getQueryString("xianshi");
var virtual = getQueryString("virtual");
var ci = getQueryString("ci");
var myDate = new Date;
var searchTimes = myDate.getTime();

$(function () {
    //获取列表数据
    get_list();
    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            //滚到页面底部，继续加载
            get_list();
        }
    });
    
});

//规则交互事件
$(".spike_close img").on("click",function(){
	$(".spike_rule").hide();
});
$(".head_pic").on("click",function(){
	$(".spike_rule").show();
});

//获取列表数据
function get_list() {
    $(".loading").remove();
    //默认第一次加载
    if (!hasmore) {
        return false;
    }
    param = {};
    param.page = curpage;
    param.pagesize = pagesize;
    if (gc_id != "") {
        param.gc_id = gc_id;
    } else if (keyword != "") {
        param.keyword = keyword;
    } else if (b_id != "") {
        param.b_id = b_id;
    }
    if (key != "") {
        param.key = key;
    }
    if (order != "") {
        param.order = order;
    }
    $.getJSON(ApiUrl + "/api.seckilljobs/active" + window.location.search.replace("?", "&"), param, function (e) {
    	console.log(e);
        if (!e) {
            e = [];
            e.result = [];
            e.result.goods_list = []
        }
        $(".loading").remove();
        curpage++;
        var r = template("home_body", e);
        $("#product_list .goods-secrch-list").append(r);
        if(e.goods.length >= 10){
            hasmore = true;
        }else{
            hasmore = false;
        }
    
    	//计算进度条
	    $(".sale_over").each(function(i){
	    	//qty：库存    sold：销量
	    	var sold = $(this).attr("sold");
	    	var qty = $(this).attr("qty");
	    	$(this).css("width",""+parseFloat(sold/qty*100)+"%");
	    });
    });
}
