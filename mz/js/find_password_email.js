$(function(){
    var uid = GetQueryString("uid");
    var hash = GetQueryString("hash");

	$('.find-password-btn').tap(function(){
		var password = $('.password-input').val();
		var reg = /^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/
		if(password == ''){
			$.dialog({
				content: '请输入您的密码',
				title: "alert",
				time: 2000
			});			
		}else if(!reg.test(password)){ 
			$.dialog({
				content: '密码为6-20位字母、数字或符号',
				title: "alert",
				time: 2000
			});			
		}else{
			$.ajax({
				type:'get',
				url:ApiUrl+"/index.php?act=mz_login&op=email_set_password",	
				data:{uid:uid,hash:hash,password:password},
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						$.dialog({
							content: result.datas,
							title: "ok",
							time: 1000
						});			
						window.setTimeout(function(){location.href = MzSiteUrl+'/login.html';},1000); 
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