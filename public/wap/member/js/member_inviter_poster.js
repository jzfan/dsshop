$(function() {
    var e = getCookie("key");
    $.getJSON(ApiUrl + "/member/get_inviter_code?key="+e, function(e) {
//  $.getJSON(ApiUrl + "/memberinviter/index.html?key="+e, function(e) {
        checkLogin(e.login);
        if (e.result.refer_qrcode_logo == null) {
                    return false
                }
        var t = e.result;
        $('#foo').val(t.inviter_url);
        t.WapSiteUrl = WapSiteUrl;
        var r = template("member_poster", t);
        $("#poster").html(r);
    })
});