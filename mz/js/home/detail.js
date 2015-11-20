// JavaScript Document

$(function(){
    var key = getcookie('key');
    var goods_id = GetQueryString("id");
	var page = 1;
	var clock = 0;
	var tab = 0;
	set_title('商品详情');
	//获取商品
    $.ajax({
		url:ApiUrl+"/index.php?act=mz_goods&op=goods_detail",
		type:"get",
		data:{goods_id:goods_id,key:key},
		dataType:"json",
        success: function(result) {
			var data = result.datas;
			if(!data.error){
				//海外购
				if(data.goods_info.goods_type>0)$('body').addClass('oversea');
            	//商品图片
				var html = template('slider-template', data);
				$('.swipe-wrap').html(html);
            	//倒计时
				if(data.goods_info.remaining_time>0){
					var t = data.goods_info.remaining_time;
					var r_t = setInterval(function(){
						var d=Math.floor(t/60/60/24);
						if(d < 10 )d = '0'+d;
						var h=Math.floor(t/60/60%24);
						if(h < 10 )h = '0'+h;
						var m=Math.floor(t/60%60);
						if(m < 10 )m = '0'+m;
						var s=Math.floor(t%60);
						if(s < 10 )s = '0'+s;
						t--;
						if(t<=0){
							$('.countdown-container').hide();
							clearInterval(r_t);
						}else{
							$('.item-countdown').html('剩余'+d+'天'+h+':'+m+':'+s);
						}
					},1000);	
					$('.countdown-container').show();
				}
				var html = template('item-base-template', data);
				$('.item-base').html(html);
            	//详细描述
				var html = template('item-base-template', data);
				$('.item-base').html(html);
				//收藏
				if(data.is_favorate){
					$('.collection').addClass('collect_checked');
				}
				//商品规格格式化数据
				if(data.goods_info.spec_name){
					var goods_map_spec = $.map(data.goods_info.spec_name,function (v,i){
						var goods_specs = {};
						goods_specs["goods_spec_id"] = i;
						goods_specs['goods_spec_name']=v;
						if(data.goods_info.spec_value){
							$.map(data.goods_info.spec_value,function(vv,vi){
								if(i == vi){
									var len = 3;
									goods_specs['goods_spec_value'] = $.map(vv,function (vvv,vvi){
										var specs_value = {};
										specs_value["specs_value_id"] = vvi;
										specs_value["specs_value_name"] = vvv;
										var len1 = Math.ceil(vvv.replace(/[^\x00-\xff]/g,"01").length/4)+1;
										if(len1 > len)len = len1;
										return specs_value;
									});
									goods_specs['len']=len;
								}
							});
							return goods_specs;
						}else{
							data.goods_info.spec_value = [];
						}
					});
					data.goods_map_spec = goods_map_spec;
					//颜色和尺寸
					var html = template('item-sku-template', data);
					$('.item-sku').html(html);
					//点击商品规格，获取新的商品
					$(".item-sku a").tap(function (){
						$(this).parents('ul').find('a').removeClass("active");
						$(this).addClass("active");
						//拼接属性
						var curEle = $(".item-sku ul").find("a.active");
						var curSpec = [];
						$.each(curEle,function (i,v){
							curSpec.push(parseInt($(v).attr("sku-vid")) || 0);
						});
						var spec_string = curSpec.sort(function(a, b) { return a - b; }).join("|");
						//获取商品ID
						var spec_goods_id = data.spec_list[spec_string];
						location.href = MzSiteUrl+'/home/detail.html?id='+spec_goods_id;
					});
				}else {
					$('.item-sku').remove();
				}
				//口碑
				var html = template('item-discuss-template', data);
				$('.item-discuss').html(html);
				//进入专场
				var html = template('item-special-template', data);
				$('.item-special').html(html);
				//详情
				var html = template('item-detail-template', data);
				$('.item-detail').html(html);
				$("#detail-wrapper").append(data.goods_info.goods_body);
				$("#detail-wrapper img").addClass('full');
				$('#qa-wrapper,#praise-wrapper').hide();
				
				if(data.goods_info.evaluation_count <= 0){
					$('.item-discuss').remove();
					clock = 1;
					$('#praise-wrapper').html('<div style="text-align: center">新鲜特卖,暂无评论</div>');
				}
				$('.cart-num').html(data.cart_count);
				if(data.goods_info.goods_storage>0){
					$('.cart-price').html('¥'+data.goods_info.goods_promotion_price);
				}else{
					$('.cart-add').addClass('soldout').html('商品已售罄');
				}
			}else{
				$.dialog({
					content: data.error,
					title: "alert",
					time: 2000
				});			
				window.setTimeout(function(){history.back();},2000); 
			}
		},
		complete: function(){
			var img_num = $('.swipe-wrap img').length;
			$('.slider-index').html('1/'+img_num);
			$('img.lazy').picLazyLoad();
			window.mySwipe = new Swipe(document.getElementById('slider'), {
				transitionEnd: function(index, elem) {
					$('.slider-index').html((index+1)+'/'+img_num);
				}
			});
			//收藏
			$(".right-collection").tap(function (){
				if(!$(this).hasClass('collect_checked')){
					if(key==''){
						location.href = MzSiteUrl+'/home/login.html';
					}else{
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
										$('.collection').addClass('collect_checked');
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
				}
			});
			//口碑
			$('.more-discuss').tap(function(){
				$('#praise').addClass('active').siblings().removeClass('active');
				$('.J_tab').hide();
				$('#praise-wrapper').show();
				$('body').scrollTop($('.item-detail').offset().top);
			})
			//详情导航
			$('.nav-wrapper li').tap(function(){
				$(this).addClass('active').siblings().removeClass('active');
				$('.J_tab').hide().eq($(this).index()).show();
				if($(this).index()==1){
					tab = 1;
					ajax_praise(0);
				}
			})
			$('.question-container').tap(function(){
				if($(this).find('.down-arrow').length>0){
					$(this).find('i').addClass('up-arrow').removeClass('down-arrow');
					$(this).find('p').removeClass('hidden');
				}else{
					$(this).find('i').removeClass('up-arrow').addClass('down-arrow');
					$(this).find('p').addClass('hidden');
				}
			})
			//评论
			$('.praise-button a').tap(function(){
				$('.praise-button a').removeClass('active');
				$(this).addClass('active');
				page = 1;
				clock = 0;
				ajax_praise($(this).attr('data'));
			})
			
			$(window).scroll(function() {
				if(!clock && tab){
					if($('.footer').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
						ajax_praise();
					}
				}					  

				if($('.item-detail').offset().top < $(window).scrollTop() + $('.navbar').height()){
					$('.nav-box').addClass('item-detail-fixed').parent().css('padding-top',$('.navbar').height());
				}else{
					$('.nav-box').removeClass('item-detail-fixed').parent().css('padding-top','0');
				}
			});
			
            //加入购物车
            $(".cart-add").tap(function (){
				if(!$(this).hasClass('soldout')){
					if(key==''){
						location.href = MzSiteUrl+'/home/login.html';
					}else{
						$.ajax({
							url:ApiUrl+"/index.php?act=mz_member_cart&op=cart_add",
							type:"post",
							data:{key:key,goods_id:goods_id,quantity:1},
							dataType: 'json',
							success:function (result){
								if(checklogin(result.login)){
									if(!result.datas.error){
										var num = parseInt($('.cart-num').html());
										$('.cart-num').html(++num)
										$('.cart-hint').css('height','1.7rem');
										setTimeout(function(){$('.cart-hint').css('height','0')},2000);
									}else{
										$.dialog({
											content: result.datas.error,
											title: "alert",
											time: 2000
										});	
									}
								}
							}
						})
					}
				}
            });
        }
    });
	function ajax_praise(type){
		if(clock || !tab)return;
		clock = 1;
		if(page==1)$('#discuss-content').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_goods&op=get_comments&goods_id='+goods_id+'&type='+type+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
				if(result.datas.goodsevallist.length>0){
					html = template('praise-discuss-template', result.datas);
					page++;
					clock = 0;
				}
				$('#discuss-content').append(html);
				$('img.lazy').picLazyLoad();
			}
		});
	}
})