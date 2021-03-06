function ncScrollLoad() {
    var page,curpage,hasmore,footer,isloading;

    ncScrollLoad.prototype.loadInit = function(options) {
        var defaults = {
                data:{},
                callback :function(){},
                resulthandle:''
            }
        var options = $.extend({}, defaults, options);
        if (options.iIntervalId) {
            pagesize = options.page>0?options.page : pagesize;
            curpage = 1;
            hasmore = true;
            footer = false;
            isloading = false;
        }
        ncScrollLoad.prototype.getList(options);
        $(window).scroll(function(){
            if (isloading) {//防止scroll重复执行
                return false;
            }
            if(($(window).scrollTop() + $(window).height() > $(document).height()-1)){
                isloading = true;
                options.iIntervalId = false;
                ncScrollLoad.prototype.getList(options);
            }
        });
    }

        ncScrollLoad.prototype.getList = function(options){
        if (!hasmore) {
            $('.loading').remove();
            ncScrollLoad.prototype.getLoadEnding();
            return false;
        }
        param = {};
        //参数
        if(options.getparam){
            param = options.getparam;
        }
        //初始化时延时分页为1
        if(options.iIntervalId){
            param.curpage = 1;
        }
        param.page = curpage;
        param.pagesize = pagesize;
        //获取数据
        $.getJSON(options.url, param, function(result){
            checkLogin(result.login);
            $('.loading').remove();
            curpage++;
            var data = result.result;

            if(options.resulthandle){
                eval('data = '+options.resulthandle+'(data);');
            }
            if (!$.isEmptyObject(options.data)) {
                data = $.extend({}, options.data, data);
            }
            //绑定获取的数据
            var html = template(options.tmplid, data);

            if(options.iIntervalId === false){
                $(options.containerobj).append(html);
            }else{
                $(options.containerobj).html(html);
            }
            //这里，原判断有误，已修复，但不确保全部。
            hasmore = result.result.hasmore;

            if (!hasmore) {
                $('.loading').hide();
                //加载底部
                if ($('#footer').length > 0) {
                    ncScrollLoad.prototype.getLoadEnding();
                    if (result.page_total == 0) {
                        $('#footer').addClass('posa');
                    }else{
                        $('#footer').removeClass('posa');
                    }
                }
            }

            if (options.callback) {
                options.callback.call('callback');
            }
            isloading = false;
        });
    }

    ncScrollLoad.prototype.getLoadEnding = function() {
        if (!footer) {
            footer = true;
            $.ajax({
                url: WapSiteUrl+'/js/footer.js',
                dataType: "script"
            });
        }
    }
}