// JavaScript Document

$(function(){
	set_title('搜索');
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_goods_class",
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
		$('.search-button').show();
	}).blur(function(){
		$(this).parent().css('width','13.976rem');
		$('.search-button').hide();
	});
	$('.search-button').tap(function(){
		if($('.search-input input').val()!=''){
			location.href = MzSiteUrl+'/home/category_list.html?key='+escape($('.search-input input').val());
		}
	});
})