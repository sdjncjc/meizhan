// JavaScript Document

$(function(){
    var gc_id_1 = GetQueryString('cate');
    var gc_id_2 = 0;
	var page = 1;
	var clock = 0;
	
	//获取品牌
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_oversea&op=get_brand&gc_id="+gc_id_1,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('ads-list-template', result.datas);
			$('.oversea-categorys-ads').html(html);
        }
    });
	
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_oversea&op=get_class2&gc_id="+gc_id_1,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			$('title,.navbar .title').html(result.datas.class_name+'专区');
			var html = template('filterbar-template', result.datas);
			$('.oversea-filterbar').html(html);
		},
		complete: function(){
			var inner_w = 5;
			$('.filterbar-inner-item').each(function(){
				inner_w += $(this).width();
			});
			$('.filterbar-inner').width(inner_w);
			
			$('.filterbar-more i').tap(function(){
				if($(this).hasClass('show-more')){
					$(this).addClass('show-less').removeClass('show-more');
					$('.filterbar-inner-mask').removeClass('hidden');
					$('.filterbar-dropdown').removeClass('hidden').css('top',$('.filterbar-inner-container').height());
					$('.filterbar-dropdown-mask').removeClass('hidden');
					$('.filterbar').removeClass('oversea-filterbar'); 
					$('.wrapper').css('padding-top','4rem');
				}else{
					$(this).addClass('show-more').removeClass('show-less');
					$('.filterbar-inner-mask').addClass('hidden');
					$('.filterbar-dropdown').addClass('hidden').css('top',0);
					$('.filterbar-dropdown-mask').addClass('hidden');
				}
			})
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				$('.filterbar-dropdown-item').eq($(this).index()).addClass('active').siblings().removeClass('active');
				$('.filterbar-inner-item').eq($(this).index()).addClass('active').siblings().removeClass('active');
				$('.filterbar-more .show-less').trigger("tap");
				gc_id_2 = $(this).attr('data-category');
				page = 1;
				clock = 0;
				ajax_get_goods_list();
			})
        }
    });
	//获取商品列表
	function ajax_get_goods_list(){
		if(clock)return;
		clock = 1;
		if(page == 1)$('.item-list').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_oversea&op=get_goods_list&gc_id_1='+gc_id_1+'&gc_id_2='+gc_id_2+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.goods_list.length>0){
					var html = template('product-list-template', result.datas);
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
	ajax_get_goods_list();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_get_goods_list();
			}
		}					  
		if($('.navbar').height()+$('.oversea-categorys-ads').height() < $(window).scrollTop()){
			$('.filterbar').removeClass('oversea-filterbar'); 
		}else{
			$('.wrapper').css('padding-top','2rem');
			$('.filterbar').addClass('oversea-filterbar'); 
		}
		if($('.filterbar').hasClass('oversea-filterbar')){
			$('.filterbar-more .show-less').trigger("tap");
		}
	});
})