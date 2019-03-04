var key = getCookie('key');
$(function() {

    //渲染list
    var load_class = new ncScrollLoad();
    load_class.loadInit({
        'url': ApiUrl + '/Membergoodsbrowse/browse_list.html',
        'getparam': {'key': key},
        'tmplid': 'viewlist_data',
        'containerobj': $("#viewlist"),
        'iIntervalId': true,
        'data': {WapSiteUrl: WapSiteUrl}
    });

    $("#clearbtn").click(function() {
        $.ajax({
            type: 'post',
            url: ApiUrl + '/Membergoodsbrowse/browse_clearall.html',
            data: {key: key},
            dataType: 'json',
            async: false,
            success: function(result) {
                if (result.code == 10000) {
                    location.href = WapSiteUrl + '/member/views_list.html';
                } else {
                    layer.open({content: result.message, btn: '我知道了'});
                }
            }
        });
    });
});

