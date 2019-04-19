$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    loadSeccode();
    $("#refreshcode").bind("click",
            function() {
                loadSeccode()
            });
    $("#mobile").on("blur",
            function() {
                if ($(this).val() != "" && !/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test($(this).val())) {
                    $(this).val(/\d+/.exec($(this).val()))
                }
            });
            
    $.ajax({
        type: "get",
        url: ApiUrl + "/member/information",
        data: {
            key: e
        },
        dataType: "json",
        success: function(e) {
        	console.log(e)
        	$(".userPic img").attr("src",e.result.member_avatar);
        	$(".uNicheng").val(e.result.member_name);
        	$(".birthday").val(e.result.member_birthday);
        	if(e.result.member_sex == 3){
        		$(".sex").eq(0).attr("checked",true);
        	}else if(e.result.member_sex == 2){
        		$(".sex").eq(1).attr("checked",true);
        	}else{
        		$(".sex").eq(2).attr("checked",true);
        	}
        }
    });
    $.sValid.init({
        rules: {
            captcha: {
                required: true,
                minlength: 4
            },
            mobile: {
                required: true,
                mobile: true
            }
        },
        messages: {
            captcha: {
                required: "请填写图形验证码",
                minlength: "图形验证码不正确"
            },
            mobile: {
                required: "请填写手机号",
                mobile: "手机号码不正确"
            }
        },
        callback: function (e, a, t) {
            if (e.length > 0) {
                var o = "";
                $.map(a,
                        function (e, a) {
                            o += "<p>" + e + "</p>"
                        });
                layer.open({content: o, skin: 'msg', time: 2});
            }
        }
    });
    
    //图片上传
    $("#userPhoto").on("change",function(e){
    	var c = getCookie("key");
    	var formData = new FormData($('#userPhoto')[0]);
    	formData.append('memberavatar', formData);
   		console.log(formData);
		$.ajax({
		    url: ApiUrl +'/member/edit_memberavatar?key='+c,
		    type: 'POST',
		    cache: false, //上传文件不需要缓存
		    data: formData,
		    processData: false,
		    contentType: false,
		    enctype:"multipart/form-data" ,
		    success: function (data) {
		    	console.log(data);
		    },
		    error: function (data) {
		         console.log("上传失败");
		    }
		}); 
    });

    
    $("#nextform").click(function() {
        var uNicheng = $.trim($(".uNicheng").val());
        var member_birthday = $.trim($(".birthday").val());
        var sex = $.trim($("input[name='sex']:checked").val());
        $.ajax({
            type: "post",
            url: ApiUrl + "/member/edit_information",
            data: {
                key: e,
                member_truename: uNicheng,
                member_birthday:member_birthday,
                member_sex:sex
            },
            dataType: "json",
            success: function(e) {
            	console.log(e);
            	if(e.code == "10000"){
            		layer.open({
					  	content:  e.message,
					  	btn: '确定',
					  	shadeClose: false,
					  	yes: function(){
					    	window.location.href = WapSiteUrl + "/member/member.html";
					  	}
					});
            	}else{
            		layer.open({content: e.message, skin: 'msg', time: 2});
            	}
            }
        })
        
    })
});