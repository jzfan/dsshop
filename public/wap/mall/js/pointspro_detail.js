var goods_id = getQueryString("id");
var map_list = [];
var map_index_id = '';
$(function (){
    var key = getCookie('key');

    var unixTimeToDateString = function(ts, ex) {
        ts = parseFloat(ts) || 0;
        if (ts < 1) {
            return '';
        }
        var d = new Date();
        d.setTime(ts * 1e3);
        var s = '' + d.getFullYear() + '-' + (1 + d.getMonth()) + '-' + d.getDate();
        if (ex) {
            s += ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
        }
        return s;
    };

    var buyLimitation = function(a, b) {
        a = parseInt(a) || 0;
        b = parseInt(b) || 0;
        var r = 0;
        if (a > 0) {
            r = a;
        }
        if (b > 0 && r > 0 && b < r) {
            r = b;
        }
        return r;
    };
    template.helper('isEmpty', function(o) {
        for (var i in o) {
            return false;
        }
        return true;
    });

    get_detail(goods_id);
 

  function contains(arr, str) {//检测goods_id是否存入
	    var i = arr.length;
	    while (i--) {
           if (arr[i] === str) {
	           return true;
           }
	    }
	    return false;
	}
  $.sValid.init({
        rules:{
            buynum:"digits"
        },
        messages:{
            buynum:"请输入正确的数字"
        },
        callback:function (eId,eMsg,eRules){
            if(eId.length >0){
                var errorHtml = "";
                $.map(eMsg,function (idx,item){
                    errorHtml += idx;
                });
                layer.open({content: errorHtml,skin: 'msg',time: 2});
            }
        }
    });
  //检测商品数目是否为正整数
  function buyNumer(){
    $.sValid();
  }
  
  function get_detail(goods_id) {
      //渲染页面
      $.ajax({
         url:ApiUrl+"/Pointprod/pinfo.html",
         type:"get",
         data:{id:goods_id,key:key},
         dataType:"json",
         success:function(result){
            var data = result.result;
            if(result.code == 10000){
             
              //渲染模板
              var html = template('product_detail', data);
              $("#product_detail_html").html(html);
			  $("#fixed-tab-pannel").html(data.goods_info.pgoods_body);
			//渲染模板
              var html = template('product_detail_sepc', data);
              $("#product_detail_spec_html").html(html);

          
              //购买数量，减
              $(".minus").click(function (){
                 var buynum = $(".buy-num").val();
                 if(buynum >1){
                    $(".buy-num").val(parseInt(buynum-1));
                 }
              });
              //购买数量加
              $(".add").click(function (){
                 var buynum = parseInt($(".buy-num").val());
                 if(buynum < data.goods_info.pgoods_storage){
                    $(".buy-num").val(parseInt(buynum+1));
                 }
              });
            
              //立即购买
             
                  $("#buy-now").click(function (){
                     var key = getCookie('key');//登录标记
                     if(!key){
                        window.location.href = WapSiteUrl+'/member/login.html';
                     }else{
                         var buynum = parseInt($('.buy-num').val()) || 0;

                      if (buynum < 1) {
                          layer.open({content: '参数错误！',skin: 'msg',time: 2});
                          return;
                      }
                      if (buynum > data.goods_info.pgoods_storage) {
                          layer.open({content: '库存不足！',skin: 'msg',time: 2});
                          return;
                      }

                        var json = {};
                        json.key = key;
                        json.pgid = goods_id;
						json.quantity = buynum;
                        $.ajax({
                            type:'post',
                            url:ApiUrl+'/Pointcart/add.html',
                            data:json,
                            dataType:'json',
                            success:function(result){
                                if (result.code != 10000) {
                                    layer.open({content: result.message, btn: '我知道了'});
                                }else{
                                    location.href = WapSiteUrl+'/order/pointbuy_step1.html';//?goods_id='+goods_id+'&buynum='+buynum;
                                }
                            }
                        });
                     }
                  });

             

                } else {
                    layer.open({
                        content:  data.error + '！<br>请返回上一页继续操作…'
                        , btn: ['返回', '取消']
                        , yes: function (index) {
                            history.back();
                            layer.close(index);
                        }
                    });
                }

            //验证购买数量是不是数字
            $("#buynum").blur(buyNumer);

			
            // 从下到上动态显示隐藏内容
            $.animationUp({
                valve : '.animation-up,#goods_spec_selected',          // 动作触发
                wrapper : '#product_detail_spec_html',    // 动作块
                scroll : '#product_roll',     // 滚动块，为空不触发滚动
                start : function(){       // 开始动作触发事件
                    $('.goods-detail-foot').addClass('hide').removeClass('block');
                },
                close : function(){        // 关闭动作触发事件
                    $('.goods-detail-foot').removeClass('hide').addClass('block');
                }
            });
			
         }
      });
  }
  
  //$.scrollTransparent();


});


function show_tip() {
    var flyer = $('.goods-pic > img').clone().css({'z-index':'999','height':'3rem','width':'3rem'});
    flyer.fly({
        start: {
            left: $('.goods-pic > img').offset().left,
            top: $('.goods-pic > img').offset().top-$(window).scrollTop()
        },
        end: {
            left: $("#cart_count1").offset().left+40,
            top: $("#cart_count1").offset().top-$(window).scrollTop(),
            width: 0,
            height: 0
        },
        onEnd: function(){
            flyer.remove();
        }
    });
}

