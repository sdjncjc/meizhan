// JavaScript Document

$(function(){
    var cate = GetQueryString("cate");
	var page = 1;
	var clock = 0;
	
	//获取品牌特卖分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_gc_id_1_list",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filterbar-template', result.datas);
			$('.filterbar').html(html);
			//设置导航
			var inner_w = 5;
			$('.filterbar-inner-item').each(function(){
				inner_w += $(this).width();
			});
			$('.filterbar-inner').width(inner_w);
			$('.filterbar-dropdown').css('top', $('.navbar').height()+$('.filterbar-inner-container').height());
			$('.filterbar-more i').tap(function(){
				$(this).toggleClass('show-more').toggleClass('show-less');
				$('.filterbar-inner-mask,.filterbar-dropdown,.filterbar-dropdown-mask').toggleClass('hidden');
			})
			$('[data="'+cate+'"]').addClass('active');
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				location.href=MzSiteUrl+'/home/brandsale_list.html?cate='+$(this).attr('data');
			})
			set_title($('.filterbar-inner .active').html());
        }
    });
	//获取品牌特卖列表
	function ajax_brandsale(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_brandsale&op=get_brandsale_list&cate='+cate+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
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