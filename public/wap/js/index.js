$(function() {
    $('.home-category .toggle').click(function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active')
            $('.category-nav').removeClass('active')
        }else{
            $(this).addClass('active')
            $('.category-nav').addClass('active')
        }
    })



    $.ajax({
        url: ApiUrl + "/index",
        type: 'get',
        dataType: 'json',
        success: function(result) {
            var data = result.result;
            //goodsclass
            for(var i in data.extral.goodsclass_list){
                $('#header .category-nav').append('<a class="category-item" href="mall/product_list.html?gc_id='+data.extral.goodsclass_list[i].gc_id+'">'+data.extral.goodsclass_list[i].gc_name+'</a>');
            }
            var html = '';
            $.each(data,function(k, v) {
                    switch (k) {
                        case 'nav_list':
                            $("#main-navlist").html(template(k, data));
                            break;
                        case 'adv_list':
                            $("#main-container1").html(template(k, data));
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
                var time = setInterval(function(){
                    var left_time=countDown(data.promotion_list[0].xianshigoods_end_time);
                    if ($.isEmptyObject(left_time)) {
                        $('.pxianshi .left_time').remove()
                        clearInterval(time);
                    } else {
                        if($('.pxianshi .day_wrap').length>0){
                            if(left_time.day>0){
                                $('.pxianshi .day').text(left_time.day);
                            }else{
                                $('.pxianshi .day_wrap').remove();
                            }
                        }
                        
                        $('.pxianshi .hour').text(left_time.hour);
                        $('.pxianshi .minute').text(left_time.minute);
                        $('.pxianshi .second').text(left_time.second);
                    }
                }, 1000);
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

});

