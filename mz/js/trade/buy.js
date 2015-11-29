$(function() {
	var key = getcookie('key');
	var ifcart = GetQueryString('ifcart');
	if(ifcart==1){
		var cart_id = GetQueryString('cart_id');
		var data = {key:key,ifcart:1,cart_id:cart_id};
	}else{
		var goods_id = GetQueryString("goods_id");
		var number = GetQueryString("buynum");
		var cart_id = goods_id+'|'+number;
		var data = {key:key,cart_id:cart_id};
	}
	
	set_title('订单确认');
	
	var payment_code = 'alipay';
    var pf = function(f) {
        return parseFloat(f) || 0;
    };
	
	// 重置红包
	var resetRpt = function() {
		var total_price = 0;
		$('.store_total').each(function(){
			total_price += pf($(this).html());
		});
		$('.zj span').html(pf(total_price));
		$('select[name=rpt]').prev().html('');
		$('select[name=rpt] option').eq(0).prop('selected', true);
	}

	$.ajax({//提交订单信息
		type:'post',
		url:ApiUrl+'/index.php?act=mz_member_buy&op=buy_step1',
		dataType:'json',
		data:data,
		success:function(result){
			var data = result.datas;
			if(typeof(data.error)!='undefined'){
				$.dialog({
					content: data.error,
					title: "alert",
					time: 1000
				});		
				window.setTimeout(function(){location.href = MzSiteUrl+'/index.html';},1000); 
				return false;
			}
			//F码购买
			if (data.is_fcode) {
				$('.fcode-box').removeClass('hidden');
			}
			
			//收获地址
			if(data.address_info != ''){
				var html = template('address-template', data);
				$('.address-box').html(html);
			}
			$('.address-box a').attr('href',MzSiteUrl+'/mine/address-manage.html?from='+encodeURIComponent(document.URL));
			
			//实名认证
			if(data.verified){
				$('.card-box').removeClass('hidden');
				$('.card-box a').attr('href',MzSiteUrl+'/mine/verified.html?from='+encodeURIComponent(document.URL));
			}
			
			//订单详情
			var html = template('orderlist-template', data);
			$('.order-box-bd').html(html);

			//总金额
			$('.zj span').html(pf(data.total_price));
			
			//选择代金券
			$('select[name=voucher]').change(function(){
				var store_id = $(this).attr('store_id');
				var varr = $(this).val();
				if(varr == 0){
					var voucher_price = 0;
				}else{
					var voucher_price = pf(varr.split('|')[2]);
				}
				var store_total = pf($('#st'+store_id).attr('store_price')) - voucher_price;
				$("#sv"+store_id).html('-'+pf(voucher_price));
				$("#st"+store_id).html(pf(store_total));

				// 重置红包
				resetRpt();
			});

			//余额结算
			var html = template('orderft-template', data);
			$('.order-box-ft').html(html);
			
//			var m = navigator.userAgent.match(/MicroMessenger\/(\d+)\./);
//			if (parseInt(m && m[1] || 0) >= 5) {
//				// in WX
//				$('.pay-box-bd .row').removeClass('hidden');
//			}
			var ua = navigator.userAgent.toLowerCase();
			if(ua.match(/MicroMessenger/i)=="micromessenger") {
				$('.pay-box-bd .row').removeClass('hidden');
			}
			
			$('.payment-method').tap(function(){
				$('.payment-method').removeClass('selected');
				$(this).addClass('selected');
				payment_code = $(this).attr('data-method');
			})
			
			$('.cart-select-dot').tap(function(){
				$(this).toggleClass('selected');
				if($('.order-mj-val .selected').length>0){
					$('.wrapper-pd').removeClass('hidden');
				}else{
					$('.wrapper-pd').addClass('hidden');
				}
			})
			
			$('select[name=rpt]').change(function(){
				var _index = $(this)[0].selectedIndex;
				if (_index == 0) {
					// 重置红包
					resetRpt();
					return false;
				}else{
					var _rptLimit = pf($(this).find('option').eq(_index).attr('data-limit'));
					var _rptPrice = pf($(this).find('option').eq(_index).attr('data-price'));
					var _totalPrice = pf($('.zj span').html());
					if (_totalPrice < _rptLimit) {	
						// 重置红包
						resetRpt();
						$.dialog({
							content: '这个红包不可以使用',
							title: "alert",
							time: 1000
						});		
					} else {
						$(this).prev().html('-'+_rptPrice);
						$('.zj span').html(pf(_totalPrice - _rptPrice));
					}
				}
			});
				
			$('.gopay-btn').tap(function(){
				if(data.no_send_tpl){
					$.dialog({
						content: '订单的收货地址超出了部分商品的配送范围，请修改订单！',
						title: "alert",
						time: 1000
					});		
					return false;
				}
				if(data.address_info == ''){
					$.dialog({
						content: '您当前还没有地址，赶快添加吧！',
						title: 'alert',
						ok: function() {
							location.href=MzSiteUrl+'/mine/address-manage.html?from='+encodeURIComponent(document.URL);
						},
						cancel: function() {}
					});
					return false;
				}
				if(data.verified){
					$.dialog({
						content: '该订单包含保税区商品，需要买家进行实名认证！',
						title: 'alert',
						ok: function() {
							location.href=MzSiteUrl+'/mine/verified.html?from='+encodeURIComponent(document.URL);
						},
						cancel: function() {}
					});
					return false;
				}
    			var _data = {};
				if(data.is_fcode){
					_data.fcode = $('input[name=fcode]').val();
					if (_data.fcode == '') {
						$.dialog({
							content: '请输入F码！',
							title: "alert",
							time: 1000
						});		
						return false;
					}
				}
				_data.key = key;
				if(ifcart == 1){//购物车订单
					_data.ifcart = ifcart;
				}
				_data.cart_id = cart_id;
				_data.address_id = data.address_info.address_id;
				_data.vat_hash = data.vat_hash;
				_data.offpay_hash = data.offpay_hash;
				_data.offpay_hash_batch = data.offpay_hash_batch;
		        _data.pay_name = 'online';
				_data.invoice_id = data.inv_info.inv_id;
				_data.rpt = $('select[name=rpt]').val();
				var voucher = [];
				$("select[name=voucher]").each(function() {
					var v = $(this).val();
					if(v)voucher.push(v);
				});
				_data.voucher = voucher.join(',');
				var pay_message = [];
				$("input[name=pay_message]").each(function() {
					var v = $(this).val();
					if(v)pay_message.push($(this).attr('store_id')+'%sid%'+v);
				});
				_data.pay_message = pay_message.join('%fenge%');
				_data.rcb_pay = 0;
				if (data.available_rc_balance > 0 && $('#usercbpay').hasClass('selected')) { // 使用充值卡
					_data.rcb_pay = 1;
					_data.password = $('input[name=paypassword]').val();
				}
				_data.pd_pay = 0;
				if(data.available_predeposit>0 && $('#usepdpy').hasClass('selected')){//使用预存款
					_data.pd_pay = 1;
					_data.password = $('input[name=paypassword]').val();
				}
				//验证密码
				if(_data.rcb_pay || _data.pd_pay){
					if(_data.password == ''){
						$.dialog({
							content: '支付密码不能为空',
							title: "alert",
							time: 1000
						});		
						return false;
					}
					$.ajax({
						type:'post',
						url:ApiUrl+'/index.php?act=mz_member_buy&op=check_password',
						data:{key:key,password:_data.password},
						dataType:'json',
						success:function(result){
							if(result.datas == 1){
								buy_step2(_data);
							}else{
								$.dialog({
									content: result.datas.error,
									title: "alert",
									time: 1000
								});
								return false;
							}
						}
					});
				}else{
					buy_step2(_data);
				}
			})
		}
	});

    function buy_step2(data){
        $.ajax({
        	type:'post',
        	url:ApiUrl+'/index.php?act=mz_member_buy&op=buy_step2',
        	data:data,
        	dataType:'json',
        	success:function(result){
        		checklogin(result.login);
                if (result.datas.error) {
					$.dialog({
						content: result.datas.error,
						title: "alert",
						time: 1000
					});
                    return false;
                }

        		if(result.datas.pay_sn != ''){
                    if (payment_code == 'alipay') {
                        location.href = ApiUrl+'/index.php?act=mz_member_payment&op=pay&key='+key+'&pay_sn='+result.datas.pay_sn;
                    }else if (payment_code == 'wxpay') {
                        location.href = ApiUrl+'/index.php?act=mz_member_payment&op=pay&key='+key+'&pay_sn='+result.datas.pay_sn+'&payment_code=wxpay_jsapi&showwxpaytitle=1';
                    }
        		}
        		return false;
        	}
        });
	}
});
