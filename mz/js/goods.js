// JavaScript Document

$(function(){
    var key = getcookie('key');
    var goods_id = GetQueryString("id");
	
	//获取商品
    $.ajax({
		url:ApiUrl+"/index.php?act=mz_goods&op=goods_detail",
		type:"get",
		data:{goods_id:goods_id,key:key},
		dataType:"json",
        success: function(result) {
			var data = result.datas;
			if(!data.error){
            	//商品图片
				var html = template('slider-template', data);
				$('.swipe-wrap').html(html);
            	//详细描述
				var html = template('item-base-template', data);
				$('.item-base').html(html);
			}else{
				$.dialog({
					content: data.error,
					title: "alert",
					time: 2000
				});			
				window.setTimeout(function(){history.back();},2000); 
			}
		},
		complete: function(){
			var img_num = $('.swipe-wrap img').length;
			$('.slider-index').html('1/'+img_num);
			$('img.lazy').picLazyLoad();
			window.mySwipe = new Swipe(document.getElementById('slider'), {
				transitionEnd: function(index, elem) {
					$('.slider-index').html((index+1)+'/'+img_num);
				}
			});
        }
    });
	//获取排行榜
	function ajax_top(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_top&op=get_list&cate='+cate+'&page='+page,
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
	//ajax_top();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				//ajax_top();
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