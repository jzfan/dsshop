var curpage = 1;
var hasMore = true;
$(function() {
	
	get_list();
	
	function get_list(){
		if(hasMore){
		    $.ajax({
		        url: ApiUrl + "/Article/article_list?page=" + curpage + "&pagesize=" + pagesize+ "&ac_id=" + 1,
		        type: 'get',
		        dataType: 'json',
		        success: function(e) {
		        	console.log(e)
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
});