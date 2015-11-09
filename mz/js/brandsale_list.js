// JavaScript Document

$(function(){
    var category = GetQueryString("category");
	var page = 1;
	var clock = 0;
	
	//获取品牌特卖分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_brandsale&op=get_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filterbar-template', result.datas);
			$('.filterbar').html(html);
		},
		complete: function(){
			show_filterbar();
			$('.filterbar-dropdown-item,.filterbar-inner-item').each(function(){
				if($(this).attr('data-category') == category){
					$(this).addClass('active');
				}else{
					$(this).removeClass('active');
				}
			});
			$('.title').html($('.filterbar-inner .active').html());
			
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				location.href=MzSiteUrl+'/brandsale/brandsale_list.html?category='+$(this).attr('data-category');
			})
        }
    });
	//获取品牌特卖列表
	function ajax_brandsale(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_list&category='+category+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
				console.log(result);
				if(result.datas.brandsale_list.length>0){
					html = template('item-list-template', result.datas);
					page++;
					clock = 0;
				}else{
					if(page == 1)html = template('empty-list-template', result.datas);
					$('.loading').hide();
				}
				$('.itemlist-brand').append(html);
				$('img.lazy').picLazyLoad();
			}
		});
	}
	ajax_brandsale();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_brandsale();
			}
		}					  
	});
})