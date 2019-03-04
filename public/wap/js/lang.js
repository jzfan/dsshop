if(getCookie('ds_mobile_lang')=='en'){
    ds_lang=ds_lang_en;
    if(typeof(ds_lang_en['title'])!='undefined'){
        document.title=ds_lang_en.title;
    }
}
$(function(){
    $('lang').each(function(){
        $(this).replaceWith(ds_lang[$(this).attr('data-id')]);
    });

})
if(typeof(template)!='undefined'){
template.helper("lang",
    function(e) {
        return ds_lang[e];
    });
}
