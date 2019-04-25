var curpage = 1;
var hasMore = true;
var keys = getCookie("key");
$(function() {
//	if(keys == ""){
////		尚未登录
//	}
	get_list();
	
	function get_list(){
		if(hasMore){
		    $.ajax({
		        url: ApiUrl + "/message/getmessagelist?page=" + curpage + "&pagesize=" + pagesize+ "&key=" + keys,
		        type: 'get',
		        dataType: 'json',
		        success: function(e) {
		        	console.log(e);
		        	curpage++;
		        	hasMore = e.result.hasmore;
		            var data = e.result;
		            
		            var html = template('article-class', data);
		            $("#article-contents").append(html);
		            
		        }
		    });
	   	}
   	}
	
    //下拉加载
    $(window).scroll(function(){
        if(($(window).scrollTop() + $(window).height() > $(document).height()-1)){
            get_list();
        }
    });
    
    
    $(document).on("click",".header-l",function(){
    	if($(this).hasClass("header-l2")){
    		$(this).parents(".arts").find(".user_msg").hide();
			$(this).parents(".arts").find(".header-l").removeClass("header-l2");
    	}else{
	    	$(".user_msg").hide();
			$(this).parents(".arts").find(".user_msg").show();
			$(".header-l").removeClass("header-l2");
			$(this).parents(".arts").find(".header-l").addClass("header-l2");
		}
    	//判断发送ajax
    	var message_id =$(this).attr("value");
    	var is_read =$(this).attr("value2");
    	var loads = $(this).parents(".arts").find(".hy_icon");
    	
    	if(is_read == 0){
    		$.ajax({
		        url: ApiUrl + "/message/updatestatus?page=" + curpage + "&pagesize=" + pagesize+ "&key=" + keys + "&message_id=" + message_id,
		        type: 'get',
		        dataType: 'json',
		        success: function(e) {
		        	if(e.code == 10000){
		        		is_read = 3;
		        		loads.html("");
		        	}
		        }
		    });
    	}
    	
	});
    
});