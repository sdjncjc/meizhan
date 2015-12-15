// JavaScript Document

$(function(){
    var cate = GetQueryString("cate");
	var page = 1;
	var clock = 0;
	var _page = 1;
	var _clock = 0;
	
	//获取品牌特卖分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_gc_id_1_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filterbar-template', result.datas);
			$('.filterbar').html(html);
			//设置导航
			var inner_w = 5;
			$('.filterbar-inner-item').each(function(){
				inner_w += $(this).width();
			});
			$('.filterbar-inner').width(inner_w);
			$('.filterbar-dropdown').css('top', $('.navbar').height()+$('.filterbar-inner-container').height());
			$('.filterbar-more i').tap(function(){
				$(this).toggleClass('show-more').toggleClass('show-less');
				$('.filterbar-inner-mask,.filterbar-dropdown,.filterbar-dropdown-mask').toggleClass('hidden');
			})
			$('[data="'+cate+'"]').addClass('active');
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				if($(this).index() == 0){
					location.href=MzSiteUrl+'/'+$(this).attr('data')+'.html';
				}else if($(this).index() > 8){
					location.href=MzSiteUrl+'/home/'+$(this).attr('data')+'.html';
				}else{
					location.href=MzSiteUrl+'/home/brandsale_list.html?cate='+$(this).attr('data');
				}
			})
			set_title($('.filterbar-inner .active').html());
        }
    });
	//获取品牌特卖列表
	function ajax_brandsale(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_brandsale_list&cate='+cate+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
				if(result.datas.brandsale_list.length>0){
					html = template('brandsale-list-template', result.datas);
					page++;
					clock = 0;
				}else{
					if(!isNaN(cate)){
						ajax_goods();
					}else{
						if(page == 1)html = template('empty-list-template', result.datas);
						$('.loading').hide();
					}
				}
				$('.itemlist-brand').append(html);
				$('img.lazy').picLazyLoad();
			}
		});
	}
	ajax_brandsale();
	
	//获取商品
	function ajax_goods(){
		if(_clock)return;
		_clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_goods&op=get_category_goods&cate='+cate+'&type=gc_id_1&page='+_page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.goods_list.length>0){
					var html = template('item-list-template', result.datas);
					$('.item-list').append(html);
					$('img.lazy').picLazyLoad();
					_page++;
					_clock = 0;
				}else{
					$('.loading').hide();
				}
			}
		});
	}
	if(!isNaN(cate)){
		ajax_goods();
	}

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_brandsale();
			}
		}					  
		if(clock && !_clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_goods();
				if(_page<6){
					_clock = 0;
					$('.loading').hide();
				}
			}
		}					  
	});
})