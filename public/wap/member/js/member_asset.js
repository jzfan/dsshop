$(function () {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return;
    }
    $.getJSON(ApiUrl + "/Member/my_asset.html", {key: e}, function (e) {
        checkLogin(e.login);
        $("#predepoit").html(e.result.predepoit + " 元");
        $("#rcb").html(e.result.available_rc_balance + " 元");
        $("#voucher").html(e.result.voucher + " 张");
        $("#point").html(e.result.point + " 分");
    });
});