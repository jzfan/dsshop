$(function() {
    var e = getCookie("key");
    $.getJSON(ApiUrl + "/member/get_inviter_code?key="+e, function(e) {
//  $.getJSON(ApiUrl + "/memberinviter/index.html?key="+e, function(e) {
		console.log(e);
		if(e.code == "10000"){
			$(".showEWM img").attr("src",e.result.qcode_url)
			$(".poster_tip").text(e.result.title);
			$(".poster_user span em").text(e.result.uid);
		}
    	
    })
});