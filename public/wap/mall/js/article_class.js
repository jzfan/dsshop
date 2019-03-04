$(function() {

        $.ajax({
            url: ApiUrl + "/Articleclass/index.html",
            type: 'get',
            jsonp: 'callback',
            dataType: 'jsonp',
            success: function(result) {
                var data = result.result;
                data.WapSiteUrl = WapSiteUrl;
                var html = template('article-class', data);
                $("#article-content").html(html);
            }
        });
});