$(function(){
    var key = getCookie('key');
    if (!key) {
        window.location.href = WapSiteUrl + '/member/login.html';
        return;
    }
    var predepoit_=0;
	//获取预存款余额
    $.getJSON(ApiUrl + '/Member/my_asset.html', {'key': key, 'fields': 'predepoit'}, function(result) {
        var html = template('pd_count_model', result.result);
        $("#pd_count").html(html);
        predepoit_ = result.result.predepoit;
    });	
	
    var referurl = document.referrer;//上级网址
    $("input[name=referurl]").val(referurl);
    $.sValid.init({
        rules: {
            pdc_amount: "required",
            pdc_bank_name: "required",
            pdc_bank_no: "required",
            pdc_bank_user: "required",
            mobilenum: "required",
            password: "required"
        },
        messages: {
            pdc_amount: "请输入提现金额！",
            pdc_bank_name: "请输入收款银行！",
            pdc_bank_no: "请输入收款账号！",
            pdc_bank_user: "请输入开户人姓名！",
            mobilenum: "请输入手机号码！",
            password: "请输入支付密码！",
        },
        callback: function(eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function(idx, item) {
                    errorHtml += idx;
                });
                layer.open({content: errorHtml,skin: 'msg',time: 2});
            }
        }
    });
    $('#loginbtn').click(function() {
        var pdc_amount = $('#pdc_amount').val();
        var pdc_bank_name = $('#pdc_bank_name').val();
        var pdc_bank_no = $('#pdc_bank_no').val();
        var pdc_bank_user = $('#pdc_bank_user').val();
        var mobilenum = $('#mobilenum').val();
        var password = $('#password').val();
        var key = getCookie('key');
        if (key == '') {
            location.href = 'login.html';
        }
        if (parseFloat(predepoit_) < parseFloat(pdc_amount))
        {
            alert('提现金额不能大于账户余额!');
            return false;
        }
        var client = 'wap';
        if ($.sValid()) {
            $.ajax({
                type: 'post',
                url: ApiUrl + "/Recharge/pd_cash_add.html",
                data: {pdc_amount: pdc_amount, pdc_bank_name: pdc_bank_name, pdc_bank_no: pdc_bank_no, pdc_bank_user: pdc_bank_user, mobilenum: mobilenum, password: password, key: key, client: client},
                dataType: 'json',
                success: function(result) {
                    if (result.code == 10000) {
                        location.href = 'pdcashlist.html';
                    } else {
                        layer.open({content: result.message,skin: 'msg',time: 2});
                    }
                }
            });
        }
    });
});