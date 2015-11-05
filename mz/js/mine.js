$(function(){
	var key = getcookie('key');
	if (key){
		// 显示用户基本信息
		getAjaxResult(getUrl('mz_mine','getUserInfo',"key=" + key),'tpl-personal',".my-info");
		// 显示订单概况
		getAjaxResult(getUrl('mz_mine','getSimpleOrderInfo',"key=" + key),'tpl-sum-orderinfo','#sum-orderinfo');
		// 我的钱包
		getAjaxResult(getUrl('mz_mine','getUserInfo',"key=" + key),'tpl-sum-mywallet',"#sum-mywallet");
	}else{
        $(".my-info").append(template('tpl-login', {}));
		getAjaxResult(template('tpl-order-list', {}));
	}

});
function getAjaxResult(url,tpl,obj){
	var html = "";
    $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        success: function(result) {
        	html = template(tpl, result.datas);
        	$(obj).append(html);
		},
		complete: function(){
        }
    });
}
function getUrl(act,op,params){
	return ApiUrl + "/index.php?act=" + act + "&op=" + op + "&" + params;
}