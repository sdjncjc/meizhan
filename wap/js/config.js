var SiteUrl = "http://www.qinqin.net";
var ApiUrl = "http://m.qinqin.net/mobile";
var pagesize = 20;
var WapSiteUrl = "http://m.qinqin.net";
var IOSSiteUrl = "https://itunes.apple.com/us/app/qin-qin-wang/id933954574?l=zh&ls=1&mt=8";
var AndroidSiteUrl = "http://src.qinqin.net/download/app/qinqin_3.1.3.apk";

// auto url detection
(function() {
    var m = /^(https?:\/\/.+)\/wap/i.exec(location.href);
    if (m && m.length > 1) {
        SiteUrl = m[1] + '/shop';
        ApiUrl = m[1] + '/mobile';
        WapSiteUrl = m[1] + '/wap';
    }
})();
