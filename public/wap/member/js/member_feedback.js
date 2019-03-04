$(function() {
    var e = getCookie("key");
    if (e === null) {
        window.location.href = WapSiteUrl + "/member/login.html";
        return
    }
    $("#feedbackbtn").click(function() {
        var a = $("#feedback").val();
        if (a == "") {
            layer.open({content: '请填写反馈内容',skin: 'msg',time: 2});
            return false
        }
        $.ajax({url: ApiUrl + "/Memberfeedback/feedback_add.html", type: "post", dataType: "json", data: {key: e, feedback: a}, success: function(e) {
                if (checkLogin(e.login)) {
                    if (e.code == 10000) {
                        layer.open({content: '提交成功', skin: 'msg', time: 2});
                        setTimeout(function() {
                            window.location.href = WapSiteUrl + "/member/member.html"
                        }, 3e3)
                    } else {
                        layer.open({content: e.message, skin: 'msg', time: 2});
                    }
                }
            }})
    })
});