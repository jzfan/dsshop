var local;
var map;
$(function() {
    var a = getQueryString("address_id");
    var e = getCookie("key");
    $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_info.html", data: {key: e, address_id: a}, dataType: "json", success: function(a) {
            checkLogin(a.login);
            $("#true_name").val(a.result.address_info.address_realname);
            $("#mob_phone").val(a.result.address_info.address_mob_phone);
            $("#area_info").val(a.result.address_info.area_info).attr({"data-areaid": a.result.address_info.area_id, "data-areaid2": a.result.address_info.city_id});
            $("#address").val(a.result.address_info.address_detail);
            $("#longitude").val(a.result.address_info.address_longitude);
            $("#latitude").val(a.result.address_info.address_latitude);
            var e = a.result.address_info.address_is_default == "1" ? true : false;
            $("#is_default").prop("checked", e);
            if (e) {
                $("#is_default").parents("label").addClass("checked")
            }
        }});
    $.sValid.init({rules: {true_name: "required", mob_phone: "required", area_info: "required", address: "required"}, messages: {true_name: "姓名必填！", mob_phone: "手机号必填！", area_info: "地区必填！", address: "街道必填！"}, callback: function(a, e, r) {
            if (a.length > 0) {
                var d = "";
                $.map(e, function(a, e) {
                    d += "<p>" + a + "</p>"
                });
                layer.open({content: d, skin: 'msg', time: 2});
            }
        }});
    $(".btn").click(function() {
        if ($.sValid()) {
            var r = $("#true_name").val();
            var d = $("#mob_phone").val();
            var i = $("#address").val();
            var s = $("#area_info").attr("data-areaid2");
            var t = $("#area_info").attr("data-areaid");
            var n = $("#area_info").val();
            var o = $("#is_default:checked").val() ? 1 : 0;
            var lat = $("#latitude").val();
            var lng = $("#longitude").val();
            $.ajax({type: "post", url: ApiUrl + "/Memberaddress/address_edit.html", data: {key: e, true_name: r, mob_phone: d, city_id: s, area_id: t, address: i, area_info: n, is_default: o, address_id: a,latitude:lat,longitude:lng}, dataType: "json", success: function(a) {
                    if (a.code == 10000) {
                        location.href = WapSiteUrl + "/member/address_list.html"
                    } else {
                        alert(a.message);
                    }
                }})
        }
    });
    $("#area_info").on("click", function() {
        $.areaSelected({success: function(a) {
                $("#area_info").val(a.area_info).attr({"data-areaid": a.area_id, "data-areaid2": a.area_id_2 == 0 ? a.area_id_1 : a.area_id_2});
                change_map(a.area_info);
            }})
    })
    $(".public-pos").on("click", function() {
        $.addressSelected({success: function(a) {
                $('#latitude').val(a.lat);
                $('#longitude').val(a.lng);
                $('#address').val(a.address);
            }})
    })
    $.ajax({
        url: ApiUrl + "/Index/get_baidu_ak.html",
        async:false,
        dataType: "json",
        success: function(e) {
            loadScript("https://api.map.baidu.com/getscript?v=2.0&ak="+e.result.baidu_ak+"&services=&t=",function(){
                init_map();
            });
        }
    })
    
    
});
function init_map(){
    map = new BMap.Map('mymap');
    var lng=$('#longitude').val();
    var lat=$('#latitude').val();
    if(lng=='' && lat==''){
    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function (r) {
        if (this.getStatus() == BMAP_STATUS_SUCCESS) {
            var lng = r.point.lng;
            var lat = r.point.lat;
            var point = new BMap.Point(lng, lat);
            map.centerAndZoom(point, 16);
            document.getElementById("longitude").value = lng;
            document.getElementById("latitude").value = lat;

        } else {
            alert('failed' + this.getStatus());
        }
    }, {enableHighAccuracy: true})
    }else{
        var point = new BMap.Point(lng, lat);
        map.centerAndZoom(point, 16);
    }
    var options = {
        onSearchComplete: function (results) {
            // 判断状态是否正确
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                if (results.getCurrentNumPois() > 0) {
                    document.getElementById("longitude").value = results.getPoi(0).point.lng;
                    document.getElementById("latitude").value = results.getPoi(0).point.lat;

                }
            }
        }
    };
    local = new BMap.LocalSearch(map, options);
}
    function change_map(name){
            if(name!=''){
                map.centerAndZoom(name,16);
                map.setCurrentCity(name);
                local.search(name);
//                var point=map.getCenter();
//                document.getElementById("longitude").value = point.lng;
//                document.getElementById("latitude").value = point.lat;
            }
            
    }