function getAjaxResult(url,tpl,obj,empty_tpl){
	var html = "";
    $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        success: function(result) {
        	if(result.code == 200 && typeof(result.datas) == 'object'){
	        	html = template(tpl, result);
	        }else{
	        	html = template(empty_tpl, {});
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
function open_url(type,sub,id){
	var key = getcookie('key');
	var url = "/login.html";
	if (key) {
		switch(type){
			case 'orders':
				if (sub == 'all') {
					url = "/mine/orders.html";
				}else if(sub == 'detail'){
					url = "/mine/orderinfo.html?id="+id;
				}

				break;
		}
	}
	window.location.href = url;
}
function ajax_do(url){
    $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        success: function(result) {
        	if(result.code == 200){
        		if (result.datas.code == 1) {
        			location.reload();
        		}else{
        			alert(result.datas.message);
        		}
	        }else{
	        	alert(result.message);
	        }
	        $(obj).append(html);
			$('img.lazy').picLazyLoad();
		},
		complete: function(){
        }
    });
}