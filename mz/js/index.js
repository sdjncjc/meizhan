// JavaScript Document

$(function(){
	//获取品牌特卖分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filterbar-template', result.datas);
			$('.filterbar').html(html);
		},
		complete: function(){
			show_filterbar();
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				location.href=MzSiteUrl+'/brandsale/brandsale_list.html?category='+$(this).attr('data-category');
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
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('item-list-template', result.datas);
			$('.itemlist-brand').append(html);
		},
		complete: function(){
			$('img.lazy').picLazyLoad();
        }
    });
	//获取推荐商品
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_goods_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('goods-list-template', result.datas);
			$('.grid-wrap').html(html);
		},
		complete: function(){
			$('img.lazy').picLazyLoad();
        }
    });
})