// JavaScript Document

$(function(){
    var cate = GetQueryString("cate");
    var key = GetQueryString("key");
	var page = 1;
	var clock = 0;
	var _cates = 0;
	var _brands = 0;
	var _sort = 0;

	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_category&op=get_category_list&cate="+cate+"&key="+encodeURI(key),
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filter_cates_template', result.datas);
			$('.filter_cates .cates_content').html(html);
			var html = template('filter_brand_template', result.datas);
			$('.filter_brands .cates_content').html(html);
			$('.navbar .title,title').html(result.datas.title);
			$('.filter_body').css('height',$(window).height()-$('.complete_button').height()*2);
			$('.cates_button,.brands_button').tap(function(){
				if($(this).attr('data-id')==0){
					$(this).addClass('current').siblings().removeClass('current');
				}else{
					$(this).toggleClass('current');
					if(!$(this).siblings().hasClass('current')){
						$(this).siblings().first().addClass('current');
					}else{
						$(this).siblings().first().removeClass('current');
					}
				}
			})
			$('.complete_button').tap(function(){
				page = 1;
				clock = 0;
				_cates = 0;
				_brands = 0;
				$('.filter_cates .current').each(function(){
					_cates += ','+$(this).attr('data-id');
				})
				_cates.substr(2);
				$('.filter_brands .current').each(function(){
					_brands += ','+$(this).attr('data-id');
				})
				_brands.substr(2);
				$('.filter').removeClass('trans');
				ajax_goods();
			})
			$('.item-sorted-popularity').tap(function(){
				page = 1;
				clock = 0;
				_sort = 0;
				$(this).addClass('current');
				$('.item-sorted-price').removeClass('current');
				ajax_goods();
			})
			$('.item-sorted-price').tap(function(){
				page = 1;
				clock = 0;
				_sort = 1;
				$(this).addClass('current');
				$('.item-sorted-popularity').removeClass('current');
				ajax_goods();
			})
        }
    });
	$('.filter').css({'height':$(window).height(),'width':$(window).width()});
	$('.item-filter').tap(function(){
		$('.filter').addClass('trans');
	})
	$('.filter_navbar .back-btn').tap(function(){
		$('.filter').removeClass('trans');
	})
	//获取商品
	function ajax_goods(){
		if(clock)return;
		clock = 1;
		if(page == 1)$('.item-list').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_category&op=get_goods&cate='+cate+'&key='+encodeURI(key)+'&cates='+_cates+'&brands='+_brands+'&sort='+_sort+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.goods_list.length>0){
					var html = template('product-list-template', result.datas);
					$('.result-content').append(html);
					$('img.lazy').picLazyLoad();
					page++;
					clock = 0;
				}else{
					if(page == 1 && key != '')$('.item-list').html('没有相应的搜索结果');
					$('.wait').hide();
				}
			}
		});
	}
	ajax_goods();

	$(window).scroll(function() {
		if(!clock){
			if($('.wait').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_goods();
			}
		}					  
	});
})