var curpage = 1;
var hasMore = true;
var footer = false;
var reset = true;
var orderKey = "";

var card_type = $("#card_type").val();
$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/member/login.html"
    }

    //选择状态事件
    $("#card_type").change(function(){
        card_type = $(this).val();
        hasMore  = true;
        $("#vCard_all").empty();
        curpage = 1;
        t(card_type);
    });

    t(card_type);
    function t(card_type) {
        if (!hasMore) {
            curpage = 1;
            return;
        }
        var html ='';
        $(".loading").remove();
        $.ajax({
            type: "post",
            url: ApiUrl + "/member/get_pluscard.html?page=" + curpage + "&pagesize=" + pagesize,
            data: {
                key: e,
                bind_type:card_type
            },
            dataType: "json",
            success: function(e) {
                checkLogin(e.login);
                curpage++;
                //alert(e.result.lists.length);
                //console.log(e.result.lists.length);
                $.each(e.result.lists, function (i, n){
                    if(n.tp_id ==""){  //未使用
                        html +='<div class="v_cards">'
                            +'<img src="../images/vip_card_ok.png"/>'
                            +'<p class="v_card_info">'
                            +'<span class="v_card_type">未使用</span>'
                            +'<span class="v_card_num">'+ n.card_no+'</span>'
                            +'</p>'
                            +'</div>';
                    }else{
                        html +='<div class="v_cards">'
                            +'<img src="../images/vip_card_no.png"/>'
                            +'<p class="v_card_info">'
                            +'<span class="v_card_type">'+ n.tp_name+'</span>'
                            +'<span class="v_card_num">'+ n.card_no+'</span>'
                            +'</p>'
                            +'</div>';
                    }
                });
                if(e.result.lists.length < 10){
                    hasMore = false;
                }else{
                    hasMore = true;
                }
                $("#vCard_all").append(html);
                return false;
            }
        })
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            //console.log($(window).scrollTop() + $(window).height());
            t(card_type);
            hasMore = false;
            return false;
        }
    });
});
function get_footer() {
    if (!footer) {
        footer = true;
        $.ajax({
            url: WapSiteUrl + "/js/footer.js",
            dataType: "script"
        })
    }
}