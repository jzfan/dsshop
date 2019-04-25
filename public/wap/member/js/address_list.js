var fromTo = getQueryString("fromTo");
var goods_id = getQueryString("goods_id");
console.log(fromTo);
$(function() {
    var e = getCookie("key");
    if (!e) {
        location.href = "login.html"
    }
    function s() {
        $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_list.html", data: {key: e}, dataType: "json", success: function(e) {
            checkLogin(e.login);
            if (e.result.address_list == null) {
                return false
            }
            var s = e.result;
            var t = template("saddress_list", s);
            $("#address_list").empty();
            $("#address_list").append(t);
            $(".deladdress").click(function() {
                var e = $(this).attr("address_id");
                layer.open({
                    content: '确认删除吗？'
                    , btn: ['确定', '取消']
                    , yes: function (index) {
                        a(e)
                        layer.close(index);
                    }
                });
            });
            
            //特殊处理 -- 秒杀功能单独处理 666秒杀
            if(fromTo == "spike"){
            	$(".spike_chose_address").show();
            	$(".spike_chose_address").on("click",function(){
            		var address_id = $(this).parents("li").attr("value");
            		var userName = $(this).parents("li").find(".name").text();
            		var userphone = $(this).parents("li").find(".phone").text();
            		var address_info = $(this).parents("li").find("dd").text();
            		sessionStorage.setItem("spike_address_id",address_id);
            		sessionStorage.setItem("spike_userName",userName);
            		sessionStorage.setItem("spike_userphone",userphone);
            		sessionStorage.setItem("spike_address_info",address_info);
            		window.location.href = WapSiteUrl + "/act_spike/product_detail.html?id="+goods_id+"&fromTo=spike&goods_id="+goods_id;
            	});
            }
            
        }})
    }
    s();
    function a(a) {
        $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_del.html", data: {address_id: a, key: e}, dataType: "json", success: function(e) {
            checkLogin(e.login);
            if (e) {
                s()
            }
        }})
    }}
);