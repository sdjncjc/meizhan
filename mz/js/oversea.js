// JavaScript Document

$(function(){
    var rec_id = GetQueryString("rec_id");
	var page = 1;
	var clock = 0;
	
	//获取品牌特卖详情
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_brandsale_info&type=1&rec_id="+rec_id,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var data = result.datas;
			if(data.error){
				$.dialog({
					content: data.error,
					title: "alert",
					time: 2000
				});			
				window.setTimeout(function(){history.back();},1000);
				return false;
			}
			var html = template('brand-story-template', data);
			$('.brand-story').html(html);
			set_title(data.brandsale.name);
			//展开
			$('.show-control').tap(function(){
				var m_h = $(this).index()==3 ? '100%' : '1.6rem';
				$('.brand-intro').css('max-height',m_h);
				$('.show-more,.show-less').toggleClass('hidden');
			});
        }
    });
	//获取商品
	function ajax_goods(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_brandsale_goods&rec_id='+rec_id+'&page='+page,
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