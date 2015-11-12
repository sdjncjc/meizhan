// JavaScript Document

$(function(){
    var rec_id = GetQueryString("rec_id");
	var page = 1;
	var clock = 0;
	
	//获取品牌特卖详情
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_oversea&op=get_info&rec_id="+rec_id,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var data = result.datas;
			var html = template('brand-story-template', data);
			$('.brand-story').html(html);
			$('.title, title').html(data.brandsale.name);
			//展开
			$('.show-more').tap(function(){
				$(this).hide();
				$('.show-less').show();
				$('.brand-intro').css('max-height','100%');
			})
			$('.show-less').tap(function(){
				$(this).hide();
				$('.show-more').show();
				$('.brand-intro').css('max-height','1.6rem');
			})
        }
    });
	//获取商品
	function ajax_goods(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_oversea&op=get_goods&rec_id='+rec_id+'&page='+page,
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
	ajax_goods();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_goods();
			}
		}					  
	});
})