var order = {
	status:GetQueryString("status"),
	init:function(type){
		if (type == 'orders') {
	        var title = "全部订单";
	        switch(this.status){
	            case '10':
	                title = "待付款";
	                break;
	            case '20':
	                title = "待发货";
	                break;
	            case '30':
	                title = "待收货";
	                break;
	            case '40':
	                title = "待评价";
	                break;
	            default:
	            	this.status = 'all';
	        }
	        set_title(title);
	        this.ajax_getOrder();
		}else if(type == "orderinfo"){
        	set_title("订单详情");
        	this.getOrderInfo();
        }else if (type == "request-return") {
        	set_title("申请售后");
        	this.getReasonInfo();
		}else if (type == "salesupport") {
        	set_title("我的售后");
	        this.getSaleSupport();
		}else if (type == "salesupport-detail") {
        	set_title("售后详情");
	        this.getSaleSupportInfo();
		};
	},
	ajax_getOrder:function(){
        if(stop)return;
        var url = getUrl('mz_member_order','getOrders',"type="+this.status+"&page=" + page);
        getAjaxResult(url,'order-list-template',".orders",'order-empty-template',"order.bindAsynEvent");
	},
	bindAsynEvent:function(data){
		var _this = this;
		// 删除订单
		$(".delOrder").tap(function(){
			_this.delOrder($(this).attr("data-order-id"));
		});
		// 取消订单
		$(".dropOrder").tap(function(){
			_this.cancelOrder($(this).attr("data-order-id"));
		});
		// 支付订单
		$(".payOrder").tap(function(){
			var pay_sn = $(this).attr("data-tid");
			_this.payOrder(pay_sn);
		});
		// 确认收货
		$(".makePoint").tap(function(){
			_this.receiveOrder($(this).attr("data-order-id"));
		});
		// 订单评价
		$(".comment").tap(function(){
			_this.commentOrder($(this).attr("data-order-id"));			
		})
	},
	getOrderInfo: function(){
        var order_id = GetQueryString("id");
        // 获取订单详情
        getAjaxResult(getUrl('mz_member_order','getOrderInfo','order_id='+order_id),'order-template',".orders");
	},
	getReasonInfo:function(){
        var order_id = GetQueryString("id");
        var goods_id = GetQueryString("goods_id");
        getAjaxResult(getUrl('mz_member_order','getReasonInfo','order_id='+order_id+"&goods_id="+goods_id),'reason-template','#reason_select','',"order.fillRequestInfo");
	},
	fillRequestInfo:function(data){
		var _this = this;
		var pay_money = 0;
		if (data.order_info.goods_list.rec_id > 0) {
			$(".refund_price").val(data.order_info.goods_list.goods_pay_price);
			pay_money = data.order_info.goods_list.goods_pay_price;
		}else{
			$(".refund_price").val(data.order_info.allow_refund_amount);
			pay_money = data.order_info.allow_refund_amount;
		}
		$(".refund_price").blur(function(){
			if (this.value > pay_money) {
				$.dialog({
					content : '退款金额不能大于' + pay_money,
					title: "alert",
					time : 2000
				});
				this.value = pay_money;
				return;
			};
		});
		$(".uploadfile").on("change", function(c) {
			$(c.target).attr("readonly", "true");
			$(c.target).parent().find(".add").html("");
		});
		$("#post_form1").attr("action",getUrl('mz_member_order','addRefund',"order_id=" + data.order_info.order_id + "&goods_id=" + data.order_info.goods_list.rec_id));
		$(".goSubmit").tap(function(){
			$("#post_form1").submit();
		});

	},
	getSaleSupport:function(){
        if(stop)return;
        var url = getUrl('mz_member_order','getSaleSupport',"page=" + page);
        getAjaxResult(url,'order-list-template',".orders",'order-empty-template');
	},
	getSaleSupportInfo:function(){
        var order_id = GetQueryString("id");
        // 获取订单详情
        getAjaxResult(getUrl('mz_member_order','getSaleSupportInfo','order_id='+order_id),'ware-template',".ware-detail",'',"order.fillRefundInfo");
	},
	fillRefundInfo:function(data){
		$("#orid").text(data.refund_sn);
		$("#create_time").text(data.add_time);
		$("#salesupport-status").text((data.refund_state==1)?"处理中":(data.refund_state==2)?"待管理员处理":"已完成");
		$("#reason").text(data.reason_info);
		$("#num").text(data.goods_num);
		$("#refund").text(data.refund_amount);
		$("#desc").text(data.buyer_message);
	},
	delOrder:function(id){
		$.dialog({
	        content : '确定要删除此订单吗？',
	        title : 'ok',
	        ok : function() {
				var url = getUrl('mz_member_order','recycleOrder','order_id='+id);
				ajax_do(url);
	        },
	        cancel : function() {},
	        lock : false
    	});
	},
	cancelOrder:function(id){
		var reason = ""
        $(".order-refund-reason").removeClass("hidden");
        $(".order-refund-reason .item").tap(function() {
            $(this).addClass("current").siblings().removeClass("current"),
            reason = $(this).attr("data-reason");
        });
	   $(".order-refund-reason .btn-ok").tap(function() {
	        if (reason){
				$.dialog({
			        content : '确定要取消订单吗？',
			        title : 'ok',
			        ok : function() {
		                var url = getUrl('mz_member_order','cancelOrder','order_id='+id + '&reason=' + reason);
		                ajax_do(url);
			        },
			        cancel : function() {},
			        lock : false
		    	});
	        }else{
				$.dialog({
					content : '请选择取消订单的原因？',
					title: "alert",
					time : 2000
				});
	        }
	    });
	    $(".order-refund-reason").on("touchend", ".btn-cancel",function() {
	        $(".order-refund-reason").addClass("hidden").find("li").removeClass("current");
	        reason = "";
	    });
	},
	payOrder:function(pay_sn){
		if(pay_sn != ''){
			var ua = navigator.userAgent.toLowerCase();
			if(ua.match(/MicroMessenger/i)=="micromessenger") {
                location.href = getUrl("member_payment",'pay','pay_sn='+pay_sn+'&payment_code=wxpay_jsapi&showwxpaytitle=1&from=mz');
			}else{
                location.href = getUrl("member_payment",'pay','pay_sn='+pay_sn);
			}
		}
	},
	receiveOrder:function(id){
		$.dialog({
	        content : '请收到商品后，再确认收货！以免造成损失。',
	        title : 'ok',
	        ok : function() {
                var url = getUrl('mz_member_order','orderReceive','order_id='+id);
                ajax_do(url);
	        },
	        cancel : function() {},
	        lock : false
    	});
	},
	commentOrder:function(){
		alert("评论");
	}

};