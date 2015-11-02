// JavaScript Document

$(function(){
	var page = 1;
	var clock = 0;
	
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_top&op=get_class",
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

		if($('body').scrollTop() > 2000){
			$('.backtop').show();
		}else{
			$('.backtop').hide();
		}
	});

	$('.backtop').tap(function(){$('body').scrollTop(0);});
})