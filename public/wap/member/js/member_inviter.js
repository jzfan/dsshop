$(function() {
    var a = getCookie("key")
    if (!a) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
        $.ajax({
            type: "post",
            url: ApiUrl + "/Memberinviter/check.html",
            data: {
                key: a
            },
            dataType: "json",
            success: function(a) {
                checkLogin(a.login);
               if(a.code!='10000'){
                   $('#inviter_msg dt').text(a.message);
                   $('#inviter_msg').show();
               }else{
                   $('#inviter_menu').show();
               }
            }
        })
    
   
    
});