$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    $.ajax({type: "get", url: ApiUrl + "/message/index.html", data: {key: e}, dataType: "json", success: function(e) {
    	console.log(e);
        if (e.code == 10000) {
        	$(".dstouch-default-list li h6").eq(0).text(e.result.article.article_list[0].article_title);
        	$(".dstouch-default-list li h6").eq(1).text(e.result.message.message_list[0].message_title);
        }
        if(e.result.message.message_list[0].message_state == 0){
        	$(".new_msgIcon").show();
        }else{
        	$(".new_msgIcon").hide();
        }
    }});
    
    
    $.ajax({type: "get", url: ApiUrl + "/Memberaccount/get_paypwd_info.html", data: {key: e}, dataType: "json", success: function(e) {
        if (e.code == 10000) {
            if (!e.result.state) {
                $("#paypwd_tips").html("未设置")
            }
        } else {
        }
    }});
});