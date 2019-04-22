$(function() {
    var ac_id = getQueryString('ac_id');
    if (ac_id == '') {
        window.location.href = WapSiteUrl + '/index.html';
        return;
    }else {
        $.ajax({
            url: ApiUrl + "/Article/article_list.html",
            type: 'get',
            data: {ac_id: ac_id},
            jsonp: 'callback',
            dataType: 'jsonp',
            success: function(result) {
            	console.log(result);
                var data = result.result;
                data.WapSiteUrl = WapSiteUrl;
                var html = template('article-list', data);
                $("#article-content").html(html);
            }
        });
    }
});