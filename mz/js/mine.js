var page = 1;
var stop = false;
function getAjaxResult(url,tpl,obj,empty_tpl){
	var html = "";
    $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        success: function(result) {
        	console.log(result);
        	if(result.code == 200 && typeof(result.datas) == 'object'){
	        	html = template(tpl, result.datas);
	        	if (typeof(result.datas.data_info) == 'object') {
	        		if (result.datas.data_info.thispage >= result.datas.data_info.totalpage) {
	        			stop = true;
                    	$(".loading").hide();
	        		};
	        	};
	        }else{
	        	if (empty_tpl != undefined) {
	        		html = template(empty_tpl, {});
	        	}else{
	        		alert(result.datas.error);
	        		history.go(-1);
	        	}
	        }
	        $(obj).append(html);
			$('img.lazy').picLazyLoad();
		},
		complete: function(){
        }
    });
}
function getUrl(act,op,params){
	var key = getcookie('key');
	if (key){
		params = "key=" + key + "&" + params;
	}
	return ApiUrl + "/index.php?act=" + act + "&op=" + op + "&" + params;
}
function ajax_do(url){
    $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        success: function(result) {
        	if(result.code == 200){
        		location.reload();
	        }else{
	        	alert(result.message);
	        }
		},
		complete: function(){
        }
    });
}

function open_url(type,sub,id){
	var key = getcookie('key');
	var url = "/login/login.html";
	if (key) {
		switch(type){
			case 'userinfo':
				url = "/mine/userinfo.html";
				break;
			case 'orders':
				if (sub == 'all') {
					url = "/mine/orders.html?status=all";
				}else if (sub== 'unpay') {
					url = "/mine/orders.html?status=10";
				}else if (sub== 'unpost') {
					url = "/mine/orders.html?status=20";
				}else if (sub== 'unget') {
					url = "/mine/orders.html?status=30";
				}else if (sub== 'unjudge') {
					url = "/mine/orders.html?status=40";
				}else if (sub== 'sold') {
					url = "/mine/orders.html?status=sold";
				}else if(sub == 'orderinfo'){
					url = "/mine/orderinfo.html?id="+id;
				};
				break;
		}
	}
	window.location.href = url;
}