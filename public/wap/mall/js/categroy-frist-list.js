var t_c=true;
var t_b=true;
$(function() {
    var e;
    $("#header").on("click", ".header-inp", function() {
        location.href = WapSiteUrl + "/mall/search.html"
    });
    $.getJSON(ApiUrl + "/goodsclass/index.html", function(t) {
        var r = t.result;
        r.WapSiteUrl = WapSiteUrl;
        var a = template("category-one", r);
        $("#categroy-cnt").html(a);
//        e = new IScroll("#categroy-cnt", {mouseWheel: true, click: true})
    });
    get_brand_recommend();
    
    $("#categroy-cnt").on("touchstart", ".category", function(e) {
        t_c=true;
    })
    $("#categroy-cnt").on("touchmove", ".category", function(e) {
        t_c=false;
    })
    $("#categroy-cnt").on("touchstart", ".brand", function(e) {
        t_b=true;
    })
    $("#categroy-cnt").on("touchmove", ".brand", function(e) {
        t_b=false;
    })
    $("#categroy-cnt").on("click", ".category", function() {
        if(!t_c){
            return;
        }
        $(".pre-loading").show();
        $(this).parent().addClass("selected").siblings().removeClass("selected");
        var t = $(this).attr("date-id");
        $.getJSON(ApiUrl + "/goodsclass/get_child_all.html", {gc_id: t}, function(e) {
            var t = e.result;
            t.WapSiteUrl = WapSiteUrl;
            var r = template("category-two", t);
            $("#categroy-rgt").html(r);
            $(".pre-loading").hide();
//            new IScroll("#categroy-rgt", {mouseWheel: true, click: true})
        });
//        e.scrollToElement(document.querySelector(".categroy-list li:nth-child(" + ($(this).parent().index() + 1) + ")"), 1e3)
    });
    $("#categroy-cnt").on("click", ".brand", function() {
        if(!t_b){
            return;
        }
        $(".pre-loading").show();
        get_brand_recommend()
    })
});
function get_brand_recommend() {
    $(".category-item").removeClass("selected");
    $(".brand").parent().addClass("selected");
    $.getJSON(ApiUrl + "/brand/recommend_list.html", function(e) {
        var t = e.result;
        t.WapSiteUrl = WapSiteUrl;
        var r = template("brand-one", t);
        $("#categroy-rgt").html(r);
        $(".pre-loading").hide();
//        new IScroll("#categroy-rgt", {mouseWheel: true, click: true})
    })
}