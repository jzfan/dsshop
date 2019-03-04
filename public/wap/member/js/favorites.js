$(function () {
    var t = getCookie("key");
    if (!t) {
        location.href = "login.html"
    }
    var i = new ncScrollLoad;
    i.loadInit({
        url: ApiUrl + "/Memberfavorites/favorites_list.html",
        getparam: {key: t},
        tmplid: "sfavorites_list",
        containerobj: $("#favorites_list"),
        iIntervalId: true,
        data: {WapSiteUrl: WapSiteUrl}
    });
    $("#favorites_list").on("click", "[ds_type='fav_del']", function () {
        var t = $(this).attr("data_id");
        if (t <= 0) {
            layer.open({content: '删除失败',skin: 'msg',time: 2});
        }
        if (dropFavoriteGoods(t)) {
            $("#favitem_" + t).remove();
            if (!$.trim($("#favorites_list").html())) {
                location.href = WapSiteUrl + "/member/favorites.html"
            }
        }
    })
});