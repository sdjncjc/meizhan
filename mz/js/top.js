// JavaScript Document

$(function(){
	title = 'TOP排行榜';
	
	var page = 1;
	var clock = 0;
	
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_gc_id_1_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('class-template', result.datas);
			$('.catebox').html(html);
        }
    });
	//获取排行榜
	function ajax_top(){
		if(clock)return;
		clock = 1;
		if(page > 10){
			$('.loading').hide();
			return;	
		}
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_top&op=get_list&page='+page,
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