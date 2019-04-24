var curpage = 1;
var page = pagesize;
var hasMore = true;
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }
    t();
    function t() {
        if(!hasMore){
            $(".loading").remove();
            return false;
        }
        $.ajax({
            type: "post",
            url: ApiUrl + "/Membervoucher/voucher_list.html?page=" + curpage + "&pagesize=" + pagesize,
            data: {
                key: e
            },
            dataType: "json",
            success: function(e) {
                $(".loading").remove();
                checkLogin(e.login);
                curpage++;
                hasMore = e.result.hasmore;
                console.log(hasMore);
                var d = e.result;
                var r = template("voucher-list-tmpl", d);
                $("#voucher-list").append(r);
            }
        })
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            t();
        }
    })
});