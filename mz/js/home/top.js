// JavaScript Document

$(function(){
    var cate = GetQueryString("cate");
	var page = 1;
	var clock = 0;
	
	//获取头部图片
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_ad&ad_side=3",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('slider-template', result.datas);
			$('.slider').html(html);
			window.mySwipe = new Swipe(document.getElementById('swipe'), {
				auto: 3000,
				callback: function(index, elem) {
					$('.slider-status span').eq(index).addClass('sel').siblings().removeClass('sel');
				}
			});
        }
    });
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_gc_id_1_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			if(cate){
				var html = template('filters-template', result.datas);
				$('.filters').html(html).removeClass('hidden');
				$('[data="'+cate+'"]').addClass('active');
				set_title($('.filter .active').html());
			}else{
				var html = template('catebox-template', result.datas);
				$('.catebox').html(html).removeClass('hidden');
				$('.banner').removeClass('hidden');
				set_title('TOP排行榜');
			}
        }
    });
	//获取排行榜
	function ajax_top(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_goods&op=get_top_list&cate='+cate+'&page='+page,
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
	ajax_top();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_top();
			}
		}					  
	});
})