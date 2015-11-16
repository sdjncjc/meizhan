// JavaScript Document

$(function(){
	set_title('购物车');
    var key = getcookie('key');
	var del_ids = [];
    if(key==''){
        location.href = MzSiteUrl+'/home/login.html';
    }else{
		init_cart_list();
	}
	//初始化页面数据
	function init_cart_list(){
		 $.ajax({
			url:ApiUrl+"/index.php?act=mz_member_cart&op=cart_list",
			type:"post",
			dataType:"json",
			data:{key:key,del_ids:del_ids},
			success:function (result){
				if(checklogin(result.login)){
					if(!result.datas.error){
						var data = result.datas;
						if(data.store_cart_list != undefined && data.store_cart_list != ''){
							var html = template('cart-list-template', data);
						}else{
							var html = template('cart-empty-template', data);
						}
						$('.cart').html(html);
						var html = template('checkout-bar-template', data);
						$('.checkout').html(html);
						
						$('.cart-select-dot').tap(function(){
							var deep = $(this).attr('data-deep');
							if(deep == 2){
								$(this).toggleClass('selected');
							}else{
								if($(this).hasClass('selected')){
									if(deep == 1){
										$(this).parents('.cart-brand-list').find('.cart-select-dot').removeClass('selected');
									}else{
										$('.cart-select-dot').removeClass('selected');
									}
								}else{
									if(deep == 1){
										$(this).parents('.cart-brand-list').find('.cart-select-dot').addClass('selected');
									}else{
										$('.cart-select-dot').addClass('selected');
									}
								}
							}
							del_ids = [];
							$('.cart-item-list .cart-select-dot').each(function(){
								if(!$(this).hasClass('selected')){
									del_ids.push($(this).attr('data-cart-id'));
								}
							})
							init_cart_list();
						})
						
						$('.reduce_num').tap(function(){
							var num = parseInt($(this).next('input').val());
							if(num > 1){
								edit_quantity($(this).attr('data-cart-id'),parseInt(num-1));
							}else{
								$.dialog({
									content: '购数量不能小于1',
									title: "alert",
									time: 2000
								});			
							}
						})
						
						$('.add_num').tap(function(){
							var num = parseInt($(this).prev('input').val());
							if(num < parseInt($(this).attr('data-limit'))){
								edit_quantity($(this).attr('data-cart-id'),parseInt(num+1));
							}else{
								$.dialog({
									content: '超过最大限购数量',
									title: "alert",
									time: 2000
								});			
							}
						})
						
						$('.cart-delete').tap(function(){
							del_cart_list($(this).attr('data-cart-id'));
						})
						
						$('.checkout-info-wrapper .button').tap(function(){
							//购物车ID
							var cart_id_arr = [];
							$('.cart-item-list .selected').each(function(){
								cart_id_arr.push($(this).attr('data-cart-id')+'|'+parseInt($(this).next().find('input').val()));
							})
							location.href = MzSiteUrl + "/trade/order/buy_step1.html?ifcart=1&cart_id="+cart_id_arr.toString();
						})
					}else{
						$.dialog({
							content: result.datas.error,
							title: "alert",
							time: 2000
						});			
					}
				}
			}
		});
	}
	//购买数量增或减
	function edit_quantity(cart_id,quantity){
		$.ajax({
			url:ApiUrl+"/index.php?act=mz_member_cart&op=cart_edit_quantity",
			type:"post",
			data:{key:key,cart_id:cart_id,quantity:quantity},
			dataType:"json",
			success:function (result){
				if(checklogin(result.login)){
					if(!result.datas.error){
						init_cart_list();
					}else{
						$.dialog({
							content: result.datas.error,
							title: "alert",
							time: 2000
						});			
					}
				}
			}
		});
	}
	//删除购物车
	function del_cart_list(cart_id){
		$.ajax({
			url:ApiUrl+"/index.php?act=mz_member_cart&op=cart_del",
			type:"post",
			data:{key:key,cart_id:cart_id},
			dataType:"json",
			success:function (result){
				if(checklogin(result.login)){
					if(!result.datas.error && result.datas == "1"){
						init_cart_list();
					}else{
						$.dialog({
							content: result.datas.error,
							title: "alert",
							time: 2000
						});			
					}
				}
			}
		});
	}
	//获取品牌特卖列表
	function ajax_group(){
		if(clock)return;
		clock = 1;
		if(page == 1)$('.group-list').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_group&op=get_list&category='+category+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
				if(result.datas.group_list.length>0){
					if(category == 'nextup'){
						html = template('list-next-up-template', result.datas);
					}else{
						html = template('list-item-template', result.datas);
					}
					page++;
					clock = 0;
				}else{
					if(page == 1)html = template('empty-list-template', result.datas);
					$('.loading').hide();
				}
				$('.group-list').append(html);
				$('img.lazy').picLazyLoad();
			},
			complete: function(){
				$('.nextup .buy-btn').tap(function(){
					var key = getcookie('key');//登录标记
					if(key==''){
						location.href = MzSiteUrl+'/home/login.html';
					}else {
						var goods_id = $(this).attr('data-iid');
						$.ajax({
							url:ApiUrl+"/index.php?act=mz_member_favorites&op=favorites_add",
							type:"post",
							dataType:"json",
							data:{goods_id:goods_id,key:key},
							success:function (result){
								if(checklogin(result.login)){
									if(!result.datas.error){
										$.dialog({
											content: result.datas,
											title: "ok",
											time: 2000
										});			
									}else{
										$.dialog({
											content: result.datas.error,
											title: "alert",
											time: 2000
										});			
									}
								}
							}
						});
					}
				})
			}
		});
	}
	//ajax_group();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				//ajax_group();
			}
		}					  
	});
})