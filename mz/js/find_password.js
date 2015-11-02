$(function(){
	$('.find-password-btn').tap(function(){
		var user = $('.user-name-input').val();
		var reg = /^1[3-578]\d{9}$/;
		var reg2 = /^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/
		if(user == ''){
			$.dialog({
				content: '请输入需要找回的手机号码/邮箱',
				title: "alert",
				time: 2000
			});			
		}else if(reg.test(user)){ 
			location.href=MzSiteUrl+"/find_password_phone.html?phone="+user;
		}else if(reg2.test(user)){ 
			$.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=mz_login&op=send_email",
				data:{email:user,site_url:MzSiteUrl},
				dataType:'json',
				success:function(result){
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
			});			
		}else{
			$.dialog({
				content: '请填写正确的手机号码或邮箱',
				title: "alert",
				time: 2000
			});			
		}
	});
});