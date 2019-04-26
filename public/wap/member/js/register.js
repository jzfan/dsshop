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
			$(".chose_city").append('<option value="-1">请设置城市</option>');
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
			        	if(res.code == 10000){
			        		layer.open({content: '短信验证码发送成功，请注意查收',skin: 'msg',time: 2});
			        		validCode=false;
					        timer=setInterval(function(){
					            if(nums>0){
					                nums--;
					                code.text(nums+"s后重新发送");
					                code.addClass("getMsgOver");
					            }else{
					                clearInterval(timer);
					                nums=60;//重置回去
					                validCode=true;
					                code.removeClass("getMsgOver");
					                code.text("发送验证码");
					    		}
					        },1000);
			        	}else{
			        		layer.open({content: res.message,skin: 'msg',time: 2});
			        	}
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
    	var username = $("#username").val();
    	var msgcode = $("#msgcode").val();
    	var leadid = $("#leadid").val();
    	var pwds = $("#pwds").val();
    	var password_confirm = $("#password_confirm").val();
    	var chose_province = $(".chose_province").val();
    	var chose_city = $(".chose_city").val();
    	
    	var provincename = $(".chose_province option").not(function(){ return !this.selected }).text();
    	var cityname = $(".cityname option").not(function(){ return !this.selected }).text();
    	
    	if(username == ""){
    		layer.open({content: "请输入手机号码", skin: 'msg', time: 2});
    		return false;
    	}else if(username.length != 11){
    		layer.open({content: "请输入正确的手机号码", skin: 'msg', time: 2});
    		return false;
    	}else if(msgcode == ""){
    		layer.open({content: "请输入短信码", skin: 'msg', time: 2});
    		return false;
    	}else if(leadid == ""){
    		layer.open({content: "请输入推荐码", skin: 'msg', time: 2});
    		return false;
    	}else if(pwds == ""){
    		layer.open({content: "请输入密码", skin: 'msg', time: 2});
    		return false;
    	}else if(password_confirm == ""){
    		layer.open({content: "请再次输入密码", skin: 'msg', time: 2});
    		return false;
    	}else if(pwds != password_confirm){
    		layer.open({content: "两次输入的密码不一致", skin: 'msg', time: 2});
    		return false;
    	}else if(chose_province == -1){
    		layer.open({content: "请设置省份", skin: 'msg', time: 2});
    		return false;
    	}else if(chose_city == -1){
    		layer.open({content: "请设置城市", skin: 'msg', time: 2});
    		return false;
    	}else{
    		var t = "wap";
    		$.ajax({
				type: "post",
				url: ApiUrl + "/login/register.html",
				data:{
					code:msgcode,
					username: username,
					password: pwds,
					password_confirm: password_confirm,
					provinceid:chose_province,
					cityid:chose_city,
					provincename:provincename,
					cityname:cityname,
					client: t,
					inviter_id:inviter_id
				},
				dataType: "json",
				success: function(e){
					console.log(e);
					$.each(e.result,function(i,c){ 
				 		html += '<option value="'+c.area_id+'">'+c.area_name+'</option>';
					});
			        $(".chose_city").append(html);
				}
			});
    		
    		
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