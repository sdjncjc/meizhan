$(function(){
	set_title('登录亲亲');
	$('body').css('background','#eee');
	$('.login-btn').tap(function(){
		var user_name = $('.user-name-input').val();
		var password = $('.password-input').val();
		if(user_name == ''){
			$.dialog({
				content: '请输入用户名/手机/邮箱',
				title: "alert",
				time: 2000
			});			
		}else if(password == ''){
			$.dialog({
				content: '请输入您的密码',
				title: "alert",
				time: 2000
			});			
		}else{
			$.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=mz_login",	
				data:{username:user_name,password:password,client:'mz'},
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						if(typeof(result.datas.key)=='undefined'){
							$.dialog({
								content: '未知错误',
								title: "alert",
								time: 2000
							});			
						}else{
							addcookie('username',result.datas.username);
							addcookie('key',result.datas.key);
							$.dialog({
								content: '登录成功',
								title: "ok",
								time: 1000
							});		
							if (getcookie('lastvisit')) {
								window.setTimeout(function(){
									window.location.href = decodeURIComponent(getcookie('lastvisit'));
								},1000); 
							}else{
								window.setTimeout(function(){history.back();},1000); 
							}
						}
					}else{
						$.dialog({
							content: result.datas.error,
							title: "alert",
							time: 2000
						});			
					}
				}
			});  
		}
	});	
});