// JavaScript Document

$(function(){
	var page = 1;
	var clock = 0;
	
	//获取团购
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_oversea&op=get_group&type=3",
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
	//获取排行榜
	function ajax_oversea(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_oversea&op=get_list&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.oversea_list.length>0){
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