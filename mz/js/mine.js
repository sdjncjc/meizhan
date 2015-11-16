var page = 1;
var stop = false;
function getAjaxResult(url,tpl,obj,empty_tpl,myfun){
	var html = "";
	var function_data;
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        success: function(result) {
        	if(result.code == 200 && (result.datas.data != undefined && result.datas.data != '')){
        		if (tpl != "" && tpl != undefined) {
		        	html = template(tpl, result.datas);
		        	if (result.datas.data_info !== undefined) {
		        		if (page >= result.datas.data_info.thispage) {
		        			if (page >= result.datas.data_info.totalpage) {
		        				stop = true;
		        			}else{
		        				stop = false;
            					page++;   
		        			}
		        		}else{
		        			stop = false;
            				page++;   
		        		}
		        	};
        		}else{
        			function_data =  result.datas.data;
        		}
	        }else{
	        	if (empty_tpl != "" && empty_tpl != undefined) {
	        		html = template(empty_tpl, {});
	        	}else{
					$.dialog({content:result.datas.error,title: "ok",time: 1000});
					// if (getcookie('lastvisit')) {
					// 	window.setTimeout(function(){
					// 		window.location.href = decodeURIComponent(getcookie('lastvisit'));
					// 	},1000); 
					// }else{
					// 	window.setTimeout(function(){history.back();},1000); 
					// }
	        	}
	        }
	        $(".loading").hide();
	        $(obj).append(html);
			$('img.lazy').picLazyLoad();
		},
		complete: function(){
			if (myfun !='' && myfun != undefined) {
				eval(myfun + "(function_data)");	
			};
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
function ajax_do(url,params){
		$.post(url,params,function(result){
	    	if(result.code == 200){
				$.dialog({content:result.datas,title: "提示",time: 1000});
				window.setTimeout(function(){location.reload();},1000); 
	        }else{
				$.dialog({content:result.datas.error,title: "ok",time: 1000});
	        }
		},'json');
}

function open_url(type,sub,id){
	var key = getcookie('key');
	var url = "/home/login.html";
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
			case 'goods':
				if (sub == 'detial') {
					url = "/detail/detail.html?id="+id;
				};
				break;
			case 'address':
				url = "/mine/address-manage.html";
				break;
			case 'coupon':
				url = "/mine/coupon.html";
				break;
			case 'point':
				url = "/mine/point.html";
				break;
			case 'balance':
				url = "/mine/balance.html";
				break;
			case 'favorite':
				url = "/mine/favorite.html";
				break;
		}
	}else{
		switch(type){
			case 'login':
				url = "/home/login.html";
				break;
			case 'register':
				url = "/home/register.html";
				break;
		}
	}
	addcookie('lastvisit',window.location.href);
	window.location.href = url;
}