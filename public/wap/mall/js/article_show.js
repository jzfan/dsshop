var e = getCookie("key");
$(function() {
	if(!e){
		e="";
	}
	
    var article_id = getQueryString('article_id');
    if (article_id == '') {
        return false;
    }else {
        $.ajax({
            url: ApiUrl + "/Article/article_show.html",
            type: 'get',
            data: {article_id: article_id,key:e},
            dataType: 'json',
            success: function(result) {
            	$(".header-title h1").text(result.result.article_title);
                var data = result.result;
                var html = template('article', data);
                $("#article-content").html(html);
                $(".article-content").html(data.article_content);
            }
        });
    }
    
});