$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    var r = getQueryString("order_id");
    $.ajax({type: "post", url: ApiUrl + "/Memberorder/search_deliver.html", data: {key: e, order_id: r}, dataType: "json", success: function(e) {
            checkLogin(e.login);
            console.log(e.result.deliver_info)
            var r = e.result;
            if (!r) {
                r = {};
                r.err = "暂无物流信息"
            }
            var t = template("order-delivery-tmpl", r);
            $("#order-delivery").html(t)
        }})
});