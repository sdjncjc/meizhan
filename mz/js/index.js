// JavaScript Document

$(function(){
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
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				location.href=MzSiteUrl+'/home/brandsale_list.html?cate='+$(this).attr('data');
			})
        }
    });
	//获取头部图片
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_slider",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('slider-template', result.datas);
			$('.slider').html(html);
        }
    });
	//获取中部广告
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_ad",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('ad-template', result.datas.ad);
			$('.ad').html(html);
        }
    });
	//获取品牌特卖列表
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_brandsale_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('item-list-template', result.datas);
			$('.itemlist-brand').append(html);
			$('img.lazy').picLazyLoad();
        }
    });
	//获取推荐商品
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_recommend_goods_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('goods-list-template', result.datas);
			$('.grid-wrap').html(html);
			$('img.lazy').picLazyLoad();
        }
    });
})