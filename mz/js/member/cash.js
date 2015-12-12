var cash = {
	type:'',
	payment_code:'',
	init:function(){
		this.type = GetQueryString("type");
		if (this.type == "") {
			return;
		};
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger") {
			$('.pay-box-bd .row').eq(1).removeClass('hidden');
			this.payment_code = "wxpay_jsapi";
		}else{
			$('.pay-box-bd .row').eq(0).removeClass('hidden');
			this.payment_code = "alipay";
		}
		switch (this.type){
			case 'recharge':
    			set_title("充值记录");
    			$('.list-page').removeClass("hidden");
    			this.getListInfo();
				break;
			case 'recharge_add':
    			set_title("在线充值");
                $(".loading").hide();
    			$('.recharge-page').removeClass("hidden");
				break;
			case 'applycash':
    			set_title("提现明细");
    			$('.list-page').removeClass("hidden");
    			this.getListInfo();
				break;
			case 'applycash_add':
	    		open_url("auth-modify","applycash_add");
				break;
		}
		this.bindEvent();
	},
	getListInfo:function(){
        if(stop) return;
		if (this.type == "recharge") {
			getAjaxResult(getUrl('mz_member_predeposit',"recharge_list","page=" + page),'recharge-list-template','.list','empty-item-template','cash.asynBindEvent');
		}else if(this.type=='applycash'){
			getAjaxResult(getUrl('mz_member_predeposit',"applycash_list","page=" + page),'pdcash-list-template','.list','empty-item-template','cash.asynBindEvent');
		}
	},
	bindEvent:function(){
		$(".sure-btn").tap(function(){
			var pdr_amount = $(".pdr_amount input").val();
    		getAjaxResult(getUrl('mz_member_predeposit',"recharge_add",'pdr_amount='+pdr_amount),'','','',"cash.recharge_pay");
		});
	},
	asynBindEvent:function(data){
		var _this = this;
		$('.list-info .pay-btn').tap(function(){
			var param = {
				pdr_sn:$(this).attr("data-pdr-sn"),
				pdr_amount:$(this).attr("data-pdr-amount")
			};
			_this.recharge_pay(param);
		});
		$(".del-btn").tap(function(){
			var pdr_sn = +$(this).attr('data-pdr-sn');
			$.dialog({
				content : '确定删除？',
				title : 'alert',
				ok : function() {
					ajax_do(getUrl('mz_member_predeposit','recharge_del','pdr_sn='+pdr_sn));
				},
				cancel : function() {},
				lock : false
			});
		});
	},
	recharge_pay:function(data){
		var content = "充值单号 : " +data.pdr_sn+",您已申请账户余额充值, 充值金额：￥"+data.pdr_amount;
		$.dialog({content:content,title: "alert",time: 3000});
		location.href = getUrl("mz_member_predeposit",'pd_order','pdr_sn='+data.pdr_sn+"&payment_code="+this.payment_code+"&from=mz");
	},
	recharge_resule:function(data){
		console.log(data);
	}
};