
$(function(){
    $('lang').each(function(){
        $(this).replaceWith(ds_lang[$(this).attr('data-id')]);
    });
    
    $('langConf').each(function(){
		$(this).replaceWith(ds_langConf[$(this).attr('data-id')]);
    });
})
if(typeof(template)!='undefined'){
	template.helper("lang",function(e) {
        return ds_lang[e];
   	});
}
if(typeof(template)!='undefined'){
	template.helper("langConf",function(e) {
        return ds_langConf[e];
   	});
}
