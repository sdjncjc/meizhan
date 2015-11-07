// JavaScript Document

$(function(){
	var category = 'all';
	var page = 1;
	var clock = 0;
	
	//获取品牌特卖分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_group&op=get_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('filterbar-template', result.datas);
			$('.filterbar').html(html);
		},
		complete: function(){
			show_filterbar();
			$('.filterbar-dropdown-item,.filterbar-inner-item').tap(function(){
				if(!$(this).hasClass('active')){
					$('.group-list').html('');
					category = $(this).attr('data-category');
					page = 1;
					clock = 0;
					$(this).addClass('active').siblings().removeClass('active');
					ajax_group();
				}
			})
        }
    });
	//获取品牌特卖列表
	function ajax_group(){
		if(clock)return;
		clock = 1;
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_group&op=get_list&category='+category+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				var html = '';
				if(result.datas.group_list.length>0){
					if(category == 'nextup'){
						html = template('list-next-up-template', result.datas);
					}else{
						html = template('list-item-template', result.datas);
					}
					page++;
					clock = 0;
				}else{
					if(page == 1)html = template('empty-list-template', result.datas);
					$('.loading').hide();
				}
				$('.group-list').append(html);
				$('img.lazy').picLazyLoad();
			},
			complete: function(){
				$('.nextup .buy-btn').tap(function(){
					var key = getcookie('key');//登录标记
					if(key==''){
						location.href = MzSiteUrl+'/login/login.html';
					}else {
						var goods_id = $(this).attr('data-iid');
						$.ajax({
							url:ApiUrl+"/index.php?act=mz_member_favorites&op=favorites_add",
							type:"post",
							dataType:"json",
							data:{goods_id:goods_id,key:key},
							success:function (result){
								if(checklogin(result.login)){
									if(!result.datas.error){
										$.dialog({
											content: result.datas,
											title: "ok",
											time: 2000
										});			
									}else{
										$.dialog({
											content: result.datas.error,
											title: "alert",
											time: 2000
										});			
									}
								}
							}
						});
					}
				})
			}
		});
	}
	ajax_group();

	$(window).scroll(function() {
		if(!clock){
			if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
				ajax_group();
			}
		}					  
	});
})