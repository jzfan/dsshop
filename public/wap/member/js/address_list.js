$(function() {
    var e = getCookie("key");
    if (!e) {
        location.href = "login.html"
    }
    function s() {
        $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_list.html", data: {key: e}, dataType: "json", success: function(e) {
                checkLogin(e.login);
                if (e.result.address_list == null) {
                    return false
                }
                var s = e.result;
                var t = template("saddress_list", s);
                $("#address_list").empty();
                $("#address_list").append(t);
                $(".deladdress").click(function() {
                    var e = $(this).attr("address_id");
                    layer.open({
                        content: '确认删除吗？'
                        , btn: ['确定', '取消']
                        , yes: function (index) {
                            a(e)
                            layer.close(index);
                        }
                    });
                })
            }})
    }
    s();
    function a(a) {
        $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_del.html", data: {address_id: a, key: e}, dataType: "json", success: function(e) {
                checkLogin(e.login);
                if (e) {
                    s()
                }
            }})
    }}
);