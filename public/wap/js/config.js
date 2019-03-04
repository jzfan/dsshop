var Domain = window.location.href;
var si=Domain.indexOf('wap');
var http = 'https:' == document.location.protocol ? 'https://': 'http://';
var SiteDomain =Domain.substring(http.length,si-1);
var SiteUrl = http+SiteDomain+"/index.php/home";
var ApiUrl = http+SiteDomain+"/index.php/mobile";
var pagesize = 10;
var WapSiteUrl = http+SiteDomain+"/wap";
var IOSSiteUrl = http+SiteDomain+"/app.ipa";
var AndroidSiteUrl = http+SiteDomain+"/app.apk";
var WeiXinOauth = true;

document.write("<script type='text/javascript' src='"+ WapSiteUrl +"/js/layer_mobile/layer.js'></script>");