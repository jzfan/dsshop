$(function() {
    var headTitle = document.title;
    var tmpl = '<div class="header-wrap">'
            + '<a href="javascript:history.back();" class="header-back"><span></span></a>'
            + '<h2>' + headTitle + '</h2>'
            + '</div>';
    var flag = getQueryString("flag");//如果是android端，隐藏掉顶部和底部
    if (flag !== 'a') {
        //渲染页面
        var html = template.compile(tmpl);
        $("#header").html(html);
    }
});