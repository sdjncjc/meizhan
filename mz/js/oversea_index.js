// JavaScript Document

$(function(){
	var page = 1;
	var clock = 0;
	
	//获取团购
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_group&op=get_oversea_list&type=3",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('oversea-promotion-tpl', result.datas);
			$('.trendy').html(html);
        }
    });
	
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_gc_id_1_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('oversea-cat-tpl', result.datas);
			$('.theme').append(html);
        }
    });
	//获取海外购品牌列表
	function ajax_oversea(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_brandsale_list&type=1&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.brandsale_list.length>0){
					var html = template('oversea-brands-tpl', result.datas);
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
	ajax_oversea();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_oversea();
			}
		}					  
	});
})