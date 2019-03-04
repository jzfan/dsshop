$(function () {
    var o = getQueryString("goods_id");
    $.ajax({
        url: ApiUrl + "/goods/goods_body",
        data: {goods_id: o},
        type: "get",
        success: function (o) {
            $(".fixed-tab-pannel").html(o)
        }
    });
    $("#goodsDetail").click(function () {
        window.location.href = WapSiteUrl + "/mall/product_detail.html?goods_id=" + o
    });
    $("#goodsBody").click(function () {
        window.location.href = WapSiteUrl + "/mall/product_info.html?goods_id=" + o
    });
    $("#goodsEvaluation").click(function () {
        window.location.href = WapSiteUrl + "/mall/product_eval_list.html?goods_id=" + o
    })
});