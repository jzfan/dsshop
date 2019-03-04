$(function () {
    $.getJSON(ApiUrl + "/Document/agreement.html?type=" + getQueryString('type'), function (a) {
        document.title = a.result.document_title;
        $("#header-title").html(a.result.document_title);
        $("#document").html(a.result.document_content)
    })

});