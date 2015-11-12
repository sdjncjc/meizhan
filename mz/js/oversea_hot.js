// JavaScript Document

$(function(){
	var page = 1;
	var type = 0;
	var clock = 0;
	
	//选择
	$('.time-tab').tap(function(){
		$(this).addClass('active').siblings().removeClass('active');
		type = $(this).index();
		var msg = '';
		if(type==1){
			msg = '明日9点准时开抢';
			$('.countdown').hide();
		}else if(type==2){
			msg = '不留遗憾，最后疯抢进行时';
			$('.countdown').hide();
		}else{
			msg = '全球爆款,限时抢购中';
			$('.countdown').show();
		}
		$('.time-countdown .title').html(msg);
		page = 1;
		clock = 0;
		ajax_oversea();
	})
	//倒计时
	var date=new Date();
	var now=date.getTime();
	date.setHours(9);
	date.setMinutes(0);
	date.setSeconds(0);
	date.setMilliseconds(0);
	var end=date.getTime();
	var t = parseInt((end-now)/1000);
	if(now > end)t = 86400+t;
	var r_t = setInterval(function(){
		var h=Math.floor(t/60/60%24);
		if(h < 10 )h = '0'+h;
		var m=Math.floor(t/60%60);
		if(m < 10 )m = '0'+m;
		var s=Math.floor(t%60);
		if(s < 10 )s = '0'+s;
		t--;
		if(t<=0){
			$('.countdown').html('');
			clearInterval(r_t);
		}else{
			$('.countdown').html('<span class="sub">距结束仅剩</span><span class="hour">'+h+'</span><span class="sub">:</span><span class="mins">'+m+'</span><span class="sub">:</span><span class="sec">'+s+'</span>');
		}
	},1000);	
	
	//获取团购
	function ajax_oversea(){
		if(clock)return;
		clock = 1;
		if(page == 1)$('.hot-list').html('');
		$.ajax({
			url: ApiUrl + '/index.php?act=mz_oversea&op=get_group&type='+type+'&page='+page,
			type: 'get',
			dataType: 'json',
			success: function(result) {
				if(result.datas.group_list.length>0){
					var html = template('oversea-hot-list-tpl', result.datas);
					$('.hot-list').append(html);
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