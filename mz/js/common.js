! function(a) {
	function b() {
		a.rem = f.getBoundingClientRect().width / 16, f.style.fontSize = a.rem + "px"
	}
        var c, d = a.navigator.appVersion.match(/iphone/gi) ? a.devicePixelRatio : 1,
		e = 1 / d,
		f = document.documentElement,
		g = document.createElement("meta");
	if (a.dpr = d, a.addEventListener("resize", function() {
			clearTimeout(c), c = setTimeout(b, 300)
		}, !1), a.addEventListener("pageshow", function(a) {
			a.persisted && (clearTimeout(c), c = setTimeout(b, 300))
		}, !1), f.setAttribute("data-dpr", d), g.setAttribute("name", "viewport"), g.setAttribute("content", "initial-scale=" + e + ", maximum-scale=" + e + ", minimum-scale=" + e + ", user-scalable=no"), f.firstElementChild) f.firstElementChild.appendChild(g);
	else {
		var h = document.createElement("div");
		h.appendChild(g), document.write(h.innerHTML)
	}
	b()
}(window);


/**
 * Zepto picLazyLoad Plugin
 * ximan http://ons.me/484.html
 * 20140517 v1.0
 */
;(function($){$.fn.picLazyLoad=function(settings){var $this=$(this),_winScrollTop=0,_winHeight=$(window).height();settings=$.extend({threshold:0,placeholder:'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC'},settings||{});lazyLoadPic();$(window).on('scroll',function(){_winScrollTop=$(window).scrollTop();lazyLoadPic();});function lazyLoadPic(){$this.each(function(){var $self=$(this);if($self.is('img')){if($self.attr('data-original')){var _offsetTop=$self.offset().top;if((_offsetTop-settings.threshold)<=(_winHeight+_winScrollTop)){$self.attr('src',$self.attr('data-original'));$self.removeAttr('data-original');}}}else{if($self.attr('data-original')){if($self.css('background-image')=='none'){$self.css('background-image','url('+settings.placeholder+')');}
var _offsetTop=$self.offset().top;if((_offsetTop-settings.threshold)<=(_winHeight+_winScrollTop)){$self.css('background-image','url('+$self.attr('data-original')+')');$self.removeAttr('data-original');}}}});}}})(Zepto);


/*! zepto.alert 30-08-2014 hihicd@hotmail.com*/
!function(a,b,c){var d=a(b),e=(a(document),1),f=!1,g=function(b){this.settings=a.extend({},g.defaults,b),this.init()};g.prototype={init:function(){this.create(),this.settings.lock&&this.lock(),isNaN(this.settings.time)||null==this.settings.time||this.time()},create:function(){var b=null==this.settings.title?"":'<div class="rDialog-header-'+this.settings.title+'"></div>',c='<div class="rDialog-wrap">'+b+'<div class="rDialog-content">'+this.settings.content+'</div><div class="rDialog-footer"></div></div>';this.dialog=a("<div>").addClass("rDialog").css({zIndex:this.settings.zIndex+e++}).html(c).prependTo("body"),a.isFunction(this.settings.ok)&&this.ok(),a.isFunction(this.settings.cancel)&&this.cancel(),this.size(),this.position()},ok:function(){var b=this,d=this.dialog.find(".rDialog-footer");a("<a>",{href:"javascript:;",text:this.settings.okText}).on("click",function(){var a=b.settings.ok();(a==c||a)&&b.close()}).addClass("rDialog-ok").prependTo(d)},cancel:function(){var b=this,d=this.dialog.find(".rDialog-footer");a("<a>",{href:"javascript:;",text:this.settings.cancelText}).on("click",function(){var a=b.settings.cancel();(a==c||a)&&b.close()}).addClass("rDialog-cancel").appendTo(d)},size:function(){{var a=this.dialog.find(".rDialog-content");this.dialog.find(".rDialog-wrap")}a.css({width:this.settings.width,height:this.settings.height})},position:function(){var a=this,b=d.width(),c=d.height(),e=0;this.dialog.css({left:(b-a.dialog.width())/2,top:(c-a.dialog.height())/2+e})},lock:function(){f||(this.lock=a("<div>").css({zIndex:this.settings.zIndex}).addClass("rDialog-mask"),this.lock.appendTo("body"),f=!0)},unLock:function(){this.settings.lock&&f&&(this.lock.remove(),f=!1)},close:function(){this.dialog.remove(),this.unLock()},time:function(){var a=this;this.closeTimer=setTimeout(function(){a.close()},this.settings.time)}},g.defaults={content:"加载中...",title:"load",width:"auto",height:"auto",ok:null,cancel:null,okText:"确定",cancelText:"取消",time:null,lock:!0,zIndex:9999};var h=function(a){new g(a)};b.rDialog=a.rDialog=a.dialog=h}(window.jQuery||window.Zepto,window);

/*!art-template - Template Engine | http://aui.github.com/artTemplate/*/
!function(){function a(a){return a.replace(t,"").replace(u,",").replace(v,"").replace(w,"").replace(x,"").split(y)}function b(a){return"'"+a.replace(/('|\\)/g,"\\$1").replace(/\r/g,"\\r").replace(/\n/g,"\\n")+"'"}function c(c,d){function e(a){return m+=a.split(/\n/).length-1,k&&(a=a.replace(/\s+/g," ").replace(/<!--[\w\W]*?-->/g,"")),a&&(a=s[1]+b(a)+s[2]+"\n"),a}function f(b){var c=m;if(j?b=j(b,d):g&&(b=b.replace(/\n/g,function(){return m++,"$line="+m+";"})),0===b.indexOf("=")){var e=l&&!/^=[=#]/.test(b);if(b=b.replace(/^=[=#]?|[\s;]*$/g,""),e){var f=b.replace(/\s*\([^\)]+\)/,"");n[f]||/^(include|print)$/.test(f)||(b="$escape("+b+")")}else b="$string("+b+")";b=s[1]+b+s[2]}return g&&(b="$line="+c+";"+b),r(a(b),function(a){if(a&&!p[a]){var b;b="print"===a?u:"include"===a?v:n[a]?"$utils."+a:o[a]?"$helpers."+a:"$data."+a,w+=a+"="+b+",",p[a]=!0}}),b+"\n"}var g=d.debug,h=d.openTag,i=d.closeTag,j=d.parser,k=d.compress,l=d.escape,m=1,p={$data:1,$filename:1,$utils:1,$helpers:1,$out:1,$line:1},q="".trim,s=q?["$out='';","$out+=",";","$out"]:["$out=[];","$out.push(",");","$out.join('')"],t=q?"$out+=text;return $out;":"$out.push(text);",u="function(){var text=''.concat.apply('',arguments);"+t+"}",v="function(filename,data){data=data||$data;var text=$utils.$include(filename,data,$filename);"+t+"}",w="'use strict';var $utils=this,$helpers=$utils.$helpers,"+(g?"$line=0,":""),x=s[0],y="return new String("+s[3]+");";r(c.split(h),function(a){a=a.split(i);var b=a[0],c=a[1];1===a.length?x+=e(b):(x+=f(b),c&&(x+=e(c)))});var z=w+x+y;g&&(z="try{"+z+"}catch(e){throw {filename:$filename,name:'Render Error',message:e.message,line:$line,source:"+b(c)+".split(/\\n/)[$line-1].replace(/^\\s+/,'')};}");try{var A=new Function("$data","$filename",z);return A.prototype=n,A}catch(B){throw B.temp="function anonymous($data,$filename) {"+z+"}",B}}var d=function(a,b){return"string"==typeof b?q(b,{filename:a}):g(a,b)};d.version="3.0.0",d.config=function(a,b){e[a]=b};var e=d.defaults={openTag:"<%",closeTag:"%>",escape:!0,cache:!0,compress:!1,parser:null},f=d.cache={};d.render=function(a,b){return q(a,b)};var g=d.renderFile=function(a,b){var c=d.get(a)||p({filename:a,name:"Render Error",message:"Template not found"});return b?c(b):c};d.get=function(a){var b;if(f[a])b=f[a];else if("object"==typeof document){var c=document.getElementById(a);if(c){var d=(c.value||c.innerHTML).replace(/^\s*|\s*$/g,"");b=q(d,{filename:a})}}return b};var h=function(a,b){return"string"!=typeof a&&(b=typeof a,"number"===b?a+="":a="function"===b?h(a.call(a)):""),a},i={"<":"&#60;",">":"&#62;",'"':"&#34;","'":"&#39;","&":"&#38;"},j=function(a){return i[a]},k=function(a){return h(a).replace(/&(?![\w#]+;)|[<>"']/g,j)},l=Array.isArray||function(a){return"[object Array]"==={}.toString.call(a)},m=function(a,b){var c,d;if(l(a))for(c=0,d=a.length;d>c;c++)b.call(a,a[c],c,a);else for(c in a)b.call(a,a[c],c)},n=d.utils={$helpers:{},$include:g,$string:h,$escape:k,$each:m};d.helper=function(a,b){o[a]=b};var o=d.helpers=n.$helpers;d.onerror=function(a){var b="Template Error\n\n";for(var c in a)b+="<"+c+">\n"+a[c]+"\n\n";"object"==typeof console&&console.error(b)};var p=function(a){return d.onerror(a),function(){return"{Template Error}"}},q=d.compile=function(a,b){function d(c){try{return new i(c,h)+""}catch(d){return b.debug?p(d)():(b.debug=!0,q(a,b)(c))}}b=b||{};for(var g in e)void 0===b[g]&&(b[g]=e[g]);var h=b.filename;try{var i=c(a,b)}catch(j){return j.filename=h||"anonymous",j.name="Syntax Error",p(j)}return d.prototype=i.prototype,d.toString=function(){return i.toString()},h&&b.cache&&(f[h]=d),d},r=n.$each,s="break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if,in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with,abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto,implements,import,int,interface,long,native,package,private,protected,public,short,static,super,synchronized,throws,transient,volatile,arguments,let,yield,undefined",t=/\/\*[\w\W]*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|"(?:[^"\\]|\\[\w\W])*"|'(?:[^'\\]|\\[\w\W])*'|\s*\.\s*[$\w\.]+/g,u=/[^\w$]+/g,v=new RegExp(["\\b"+s.replace(/,/g,"\\b|\\b")+"\\b"].join("|"),"g"),w=/^\d[^,]*|,\d[^,]*/g,x=/^,+|,+$/g,y=/^$|,+/;e.openTag="{{",e.closeTag="}}";var z=function(a,b){var c=b.split(":"),d=c.shift(),e=c.join(":")||"";return e&&(e=", "+e),"$helpers."+d+"("+a+e+")"};e.parser=function(a){a=a.replace(/^\s/,"");var b=a.split(" "),c=b.shift(),e=b.join(" ");switch(c){case"if":a="if("+e+"){";break;case"else":b="if"===b.shift()?" if("+b.join(" ")+")":"",a="}else"+b+"{";break;case"/if":a="}";break;case"each":var f=b[0]||"$data",g=b[1]||"as",h=b[2]||"$value",i=b[3]||"$index",j=h+","+i;"as"!==g&&(f="[]"),a="$each("+f+",function("+j+"){";break;case"/each":a="});";break;case"echo":a="print("+e+");";break;case"print":case"include":a=c+"("+b.join(",")+");";break;default:if(/^\s*\|\s*[\w\$]/.test(e)){var k=!0;0===a.indexOf("#")&&(a=a.substr(1),k=!1);for(var l=0,m=a.split("|"),n=m.length,o=m[l++];n>l;l++)o=z(o,m[l]);a=(k?"=":"=#")+o}else a=d.helpers[c]?"=#"+c+"("+b.join(",")+");":"="+a}return a},"function"==typeof define?define(function(){return d}):"undefined"!=typeof exports?module.exports=d:this.template=d}();

var SiteUrl = "http://www.qinqin.net";
var ApiUrl = "http://mobile.qinqin.net";
var pagesize = 10;
var MzSiteUrl = "http://mz.qinqin.net";

//记录来源推广号
var pm = GetQueryString('pm');
if(pm)addcookie('pm',pm);

function get_footer(){
	var key = getcookie('key');
	var html = '<div class="info-line">';
	if (key){
		html += '<a href="/mine/index.html">我的亲亲</a><a href="javascript:void(0);" class="logout">退出</a>';
	}else{
		html += '<a href="/home/login.html">登录</a><a href="/home/register.html">注册</a>';
	}
	html += '<a href="">客户端</a><a href="http://www.qinqin.net">电脑版</a></div><p class="info"></p><p class="tel">客服热线：<span>4000-500-775</span></p><p class="icp">2015 京ICP备08031978号</p>';
	$('.footer').html(html);
	$('.footer .logout').tap(function(){
		var username = getcookie('username');
		var key = getcookie('key');
		$.ajax({
			type:'post',
			url:ApiUrl+'/index.php?act=mz_logout',
			data:{username:username,key:key,client:'mz'},
			dataType:'json',
			success:function(result){
				if(!result.datas.error){
					delCookie('username');
					delCookie('key');
					$.dialog({
						content: result.datas,
						title: "ok",
						time: 1000
					});			
					window.setTimeout(function(){location.href = MzSiteUrl+'/index.html';},1000); 
				}else{
					$.dialog({
						content: result.datas.error,
						title: "alert",
						time: 2000
					});			
				}
			}
		});
	});
}

function GetQueryString(name){
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if (r!=null) return unescape(r[2]); return '';
}

function addcookie(name,value,expireHours){
	var cookieString=name+"="+escape(value)+"; path=/";
	//判断是否设置过期时间
	if(expireHours>0){
		var date=new Date();
		date.setTime(date.getTime() + expireHours*3600*1000);
		cookieString=cookieString+"; expires="+date.toGMTString();
	}
	document.cookie=cookieString;
}

function getcookie(name){
	var strcookie=document.cookie;
	var arrcookie=strcookie.split("; ");
	for(var i=0;i<arrcookie.length;i++){
	var arr=arrcookie[i].split("=");
	if(arr[0]==name)return arr[1];
	}
	return "";
}

function delCookie(name){//删除cookie
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval=getcookie(name);
	if(cval!=null) document.cookie= name + "="+cval+"; path=/;expires="+exp.toGMTString();
}

function checklogin(state){
	if(state == 0){
		delCookie('key');
		location.href = MzSiteUrl+'/home/login.html';
		return false;
	}else {
		return true;
	}
}

//设置标题
function set_title(title){
	$('.navbar .title').html(title);
	$('title').prepend(title+' - ');
}

$(function(){
	if($('.footer').length)get_footer();

	$('body').append('<a href="javascript:;" class="backtop"></a>');
	$('.backtop').tap(function(){$('body').scrollTop(0);});
	$(window).scroll(function() {
		if($('body').scrollTop() > 2000){
			$('.backtop').show();
		}else{
			$('.backtop').hide();
		}
	});
});
