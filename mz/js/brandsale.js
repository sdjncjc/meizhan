// JavaScript Document

$(function(){
    var rec_id = GetQueryString("rec_id");
	var page = 1;
	var clock = 0;
	var _stock = 0;
	var _cate = 0;
	var _sort = 0;
	
	//获取品牌特卖详情
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_info&rec_id="+rec_id,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var data = result.datas;
			var html = template('brand-story-template', data);
			$('.brand-story').html(html);
			var html = template('refinements-template', data);
			$('.item-nav').append(html);
			$('.title, title').html(data.brandsale.name);
			//倒计时
			if(data.brandsale.remaining_time>0){
				var t = data.brandsale.remaining_time;
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
						$('.item-countdown').html('');
						clearInterval(r_t);
					}else{
						$('.item-countdown').html('剩余'+d+'天'+h+':'+m+':'+s);
					}
				},1000);	
			}
			//展开
			$('.show-more').tap(function(){
				$(this).hide();
				$('.show-less').show();
				$('.brand-intro').css('max-height','100%');
			})
			$('.show-less').tap(function(){
				$(this).hide();
				$('.show-more').show();
				$('.brand-intro').css('max-height','1.6rem');
			})
			//选有货
			$('.item-show-stock').tap(function(){
				if(_stock){
					_stock = 0;
					$(this).removeClass('selected');
				}else{
					_stock = 1;
					$(this).addClass('selected');
				}
				page = 1;
				clock = 0;
				ajax_goods();
			})
			//人气
			$('.item-sorted-popularity').tap(function(){
				$('.item-sorted-popularity,.item-sorted-price').removeClass('current');
				if(_sort!=1){
					_sort = 1;
					$(this).addClass('current');
				}
				page = 1;
				clock = 0;
				ajax_goods();
			})
			//价格
			$('.item-sorted-price').tap(function(){
				$('.item-sorted-popularity,.item-sorted-price').removeClass('current');
				if(_sort!=2){
					_sort = 2;
					$(this).addClass('current');
				}
				page = 1;
				clock = 0;
				ajax_goods();
			})
			//筛选
			$('.item-filter').tap(function(){
				$('.refinement').toggleClass('hidden');
			})
			$('.refinement li').tap(function(){
				if(!$(this).hasClass('selected')){
					$(this).addClass('selected').siblings().removeClass('selected');
					_cate = $(this).attr('data-filter');
					if(_cate>0){
						$('.item-filter').addClass('selected');
					}else{
						$('.item-filter').removeClass('selected');
					}
					$('.refinement').addClass('hidden');
					page = 1;
					clock = 0;
					ajax_goods();
				}
			})
			$('img.lazy').picLazyLoad();
        }
    });
	//获取商品
	function ajax_goods(){
		if(clock)return;
		clock = 1;
		if(page == 1)$('.item-list').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_goods&rec_id='+rec_id+'&cate='+_cate+'&stock='+_stock+'&sort='+_sort+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.goods_list.length>0){
					var html = template('item-list-template', result.datas);
					$('.item-list').append(html);
					$('img.lazy').picLazyLoad();
					page++;
					clock = 0;
				}else{
					$('.loading').hide();
				}
			}
		});
	}
	ajax_goods();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_goods();
			}
		}					  
	});
})