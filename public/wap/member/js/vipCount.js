var curpage = 1;
var hasMore = true;
var footer = false;
var reset = true;
var orderKey = "";
var card_type = $("#card_type").val();
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }

    t();
    function t() {
        if (!hasMore) {
            curpage = 1;
            return;
        }
        $(".loading").remove();
        var t = $("#filtrate_ul").find(".selected").find("a").attr("data-state");
        var r = $("#order_key").val();
        $.ajax({
            type: "post",
            url: ApiUrl + "/plus/getCommisionList.html?page=" + curpage + "&pagesize=" + pagesize,
            data: {
                key: e
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                var html ='';
                $(".v_data_money em").html(e.result.total);
                if(e.result.lists.length == 0){
                    $(".count_no").show();
                    return false;
                }else{
                    $(".count_no").hide();
                    $.each(e.result.lists, function (i, n){
                        html +='<p class="count_data">'
                            +'<span>'+ n.member_name+'</span>'
                            +'<span>'+ n.commision+'</span>'
                            +'<span class="sp1">'+ n.create_at+'</span>'
                            +'</p>';
                    });
                }
                if(e.result.lists.length < 10){
                    hasMore = false;
                }else{
                    hasMore = true;
                }
                $(".count_all").append(html);
            }
        })
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            //console.log($(window).scrollTop() + $(window).height());
            t();
        }
    });
});
function get_footer() {
    if (!footer) {
        footer = true;
        $.ajax({
            url: WapSiteUrl + "/js/footer.js",
            dataType: "script"
        })
    }
}