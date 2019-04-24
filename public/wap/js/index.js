$(function() {
    $('.home-category .toggle').click(function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $('.category-nav').removeClass('active');
            $(".linear").show();
        }else{
            $(this).addClass('active');
            $('.category-nav').addClass('active');
            $(".linear").hide();
        }
    });



    $.ajax({
        url: ApiUrl + "/index",
        type: 'get',
        dataType: 'json',
        success: function(result) {
            console.log(result)
            var data = result.result;
            //goodsclass
            for(var i in data.extral.goodsclass_list){
                $('#header .category-nav').append('<a class="category-item" href="mall/product_list.html?gc_id='+data.extral.goodsclass_list[i].gc_id+'">'+data.extral.goodsclass_list[i].gc_name+'</a>');
            }
            //首页91go新品链接
//          for(var i in data.extral.promotion_list){
//              $('.newGoods .category-nav').append('<a class="category-item" href="mall/product_list.html?gc_id='+data.extral.goodsclass_list[i].gc_id+'">'+data.extral.goodsclass_list[i].gc_name+'</a>');
            	
//          	<a class="category-item" href="mall/product_list.html?gc_id=1">
//		        	<div class="ngds">
//		        		<div class="ngds_pic"><img src="http://www.shop.com/uploads/home/store/goods/1_2017092901284880537.jpg" /></div>
//		        		<div class="ngds_price">￥<em class="n_price">5455</em><span>送<em>5</em>积分</span></div>
//		        	</div>
//		        </a>
		        
//          }
            
            
            var html = '';
            $.each(data,function(k, v) {
                switch (k) {
                    case 'nav_list':
                        $("#main-navlist").html(template(k, data));
                        break;
                    case 'adv_list':
                        $("#main-container1").html(template(k, data));
                        break;
                    case 'forsalegoods_list':
                        $("#forsalegoods_list1").html(template(k, data));
                        break;
                    case 'extral':
                        break;
                    default:
                        html += template(k, data);
                }
            });

            $("#main-container2").html(html);
            //限时购倒计时
            if (data.promotion_list.length > 0) {
                timer(data.promotion_list[0].xianshigoods_end_time-data.promotion_list[0].request_time);
            }
            $('.adv_list').each(function() {
                if ($(this).find('.item').length < 2) {
                    $(this).find('.swipe-nav').hide();
                    return;
                }

                Swipe(this, {
                    speed: 400,
                    auto: 3000,
                    continuous: true,
                    disableScroll: false,
                    stopPropagation: false,
                    callback: function(index, elem) {
                        $(elem).parents('.adv_list').find('.swipe-nav em').removeClass('active');
                        $(elem).parents('.adv_list').find('.swipe-nav em').eq(index).addClass('active');
                    },
                    transitionEnd: function(index, elem) {}
                });
            });

        }
    });

    //重写倒计时  时间为差值
    function timer(intDiff) {
        var times= window.setInterval(function () {
            var day = 0,
                hour = 0,
                minute = 0,
                second = 0; //时间默认值
            if (intDiff > 0) {
                day = Math.floor(intDiff / (60 * 60 * 24));
                //day*24
                hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
                minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
                second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
            }
            if (minute <= 9) minute = '0' + minute;
            if (second <= 9) second = '0' + second;

            $('.pxianshi .day').text(day);
            $('.pxianshi .hour').text(hour);
            $('.pxianshi .minute').text(minute);
            $('.pxianshi .second').text(second);
            intDiff--;
            if(intDiff==0){
                clearInterval(times);
            }
        }, 1000);
    }
});

