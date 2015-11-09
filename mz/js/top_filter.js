// JavaScript Document

$(function(){
    var cate = GetQueryString("cate");
	var page = 1;
	var clock = 0;
	
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_top&op=get_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('class-template', result.datas);
			$('.filters').html(html);
		},
		complete: function(){
			$('.filter').each(function(){
				if($(this).attr('data') == cate){
					$(this).addClass('active');
				}else{
					$(this).removeClass('active');
				}
			});
			$('.filter').tap(function(){
				location.href=MzSiteUrl+'/app/top_filter.html?cate='+$(this).attr('data');
			})
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
	ajax_top();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_top();
			}
		}					  
	});
})