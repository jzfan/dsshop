$(function() {
    var e = getCookie("key");
    var inviter_id = getQueryString("inviter_id");
    if(inviter_id == ""){
    	$("#lead_id").val("");
    	$("#lead_id").removeAttr("disabled");
    }else{
    	$("#lead_id").val(inviter_id);
    	$("#lead_id").attr("disabled","true");
    }
    
    if (e) {
        window.location.href = WapSiteUrl + "/member/member.html";
        return
    }
    
    
    //获取默认地址
    $.getJSON(ApiUrl + "/login/get_area.html",{pid:"0"}, function(e) {
    	$(".chose_province").empty();
    	var html = "";
    	$(".chose_province").append('<option value="-1">请设置省份</option>');
    	$.each(e.result,function(i,c){ 
	 		html += '<option value="'+c.area_id+'">'+c.area_name+'</option>';
		});
        $(".chose_province").append(html);
    });
    
    $(".chose_province").change(function(){
		var pid = $(this).val();
		if(pid == -1){
			layer.open({content: "请设置省份", skin: 'msg', time: 2});
			return false;
		}else{
			$(".chose_city").empty();
    		var html = "";
			$.ajax({
				type: "get",
				url: ApiUrl + "/login/get_area.html",
				data:{pid:pid},
				dataType: "json",
				success: function(e){
					console.log(e);
					$.each(e.result,function(i,c){ 
				 		html += '<option value="'+c.area_id+'">'+c.area_name+'</option>';
					});
			        $(".chose_city").append(html);
				}
			});
		}
	});
	
	//发送短信事件
	$(".input-getCode").on("click",function(){
		var timer="";
		var nums=60;
		var validCode=true;
		var username = $("#username").val();
		if(username == ""){
			layer.open({content: '请输入手机号码',skin: 'msg',time: 2});
			return false;
		}else if(username.length != 11){
			layer.open({content: '请输入正确的手机号码',skin: 'msg',time: 2});
			return false;
		}else{
		    var code=$(this);
		    if(validCode){
		    	$.ajax({
			        url: ApiUrl + "/login/getcode",
			        type: 'get',
			        dataType: 'json',
			        data:{username:username},
			        success: function(res) {
			        	console.log(res);
//			        	if(res.code == 200){
//			        		layer.open({content: '短信验证码发送成功，请注意查收',skin: 'msg',time: 2});
//			        		validCode=false;
//					        timer=setInterval(function(){
//					            if(nums>0){
//					                nums--;
//					                code.text(nums+"s后重新发送");
//					                code.addClass("getMsgOver");
//					            }else{
//					                clearInterval(timer);
//					                nums=60;//重置回去
//					                validCode=true;
//					                code.removeClass("getMsgOver");
//					                code.text("发送验证码");
//					    		}
//					        },1000);
//			        	}
			        },
			        error:function(e){
			        	layer.open({content: '短信验证码发送失败，请稍候重试',skin: 'msg',time: 2});
						return false;
			        }
		        });
		    }
	    }
	})
    
    $("#registerbtn").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
//      var e = $("input[name=username]").val();
//      var r = $("input[name=pwd]").val();
//      var a = $("input[name=password_confirm]").val();
//      var i = $("input[name=email]").val();
//      var inviter_id = $("input[name=inviter_id]").val();
        var t = "wap";
        if ($.sValid()) {
            $.ajax({type: "post", url: ApiUrl + "/Login/register.html", data: {username: e, password: r, password_confirm: a, email: i, client: t,inviter_id:inviter_id}, dataType: "json", success: function(e) {
                if (e.code == 10000) {
                    if (typeof e.result.key == "undefined") {
                        return false
                    } else {
                        updateCookieCart(e.result.key);
                        addCookie("username", e.result.username);
                        addCookie("key", e.result.key);
                        location.href = WapSiteUrl + "/member/member.html"
                    }
                } else {
                    layer.open({content: e.message, skin: 'msg', time: 2});
                }
            }})
        }
    })
});