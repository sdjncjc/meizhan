// JavaScript Document

$(function(){
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_category&op=index",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('tpl', result.datas);
			$('.category').html(html);
			$('img.lazy').picLazyLoad();
        }
    });
	$('.search-input input').focus(function(){
		$(this).parent().css('width','12.476rem');
		$('.search-button').removeClass('search-button-show');
	}).blur(function(){
		$(this).parent().css('width','13.976rem');
		$('.search-button').addClass('search-button-show');
	});
	$('.search-button').tap(function(){
		if($('.search-input input').val()!=''){
			location.href = MzSiteUrl+'/category/category_list.html?key='+escape($('.search-input input').val());
		}
	});
})