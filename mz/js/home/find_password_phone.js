$(function(){
	set_title('重置密码');
	$('.home-btn').remove();
    var phone = GetQueryString("phone");

	$('.span_mobileNum').html(phone);
	var wait=60;
	function r_time() {  
        if (wait == 0) {  
            $('.pin-btn').html('获取验证码').attr('disabled',null);        
            wait = 60;  
        } else {
			$(".pin-btn").html(+wait+'秒后重试');
            wait--;  
            setTimeout(function() {  
                r_time()  
            },  
            1000)  
        }  
    }  
	$('.pin-btn').tap(function(){
		var reg = /^1[3-578]\d{9}$/;
        if(phone.length == 11 && reg.test(phone)){
			$.ajax({
				type:'get',
				url:ApiUrl+"/index.php?act=mz_login&op=get_captcha&type=3&phone="+phone,
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						$('.pin-btn').attr('disabled',true);
						r_time();
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
				content: '手机号码不正确',
				title: "alert",
				time: 2000
			});			
		}
	})
	$('.find-password-btn').tap(function(){
		var captcha = $(".pin-input").val();
		var password = $('.password-input').val();
		var reg = /^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/
		if(captcha == ''){
			$.dialog({
				content: '请输入您的短信验证码',
				title: "alert",
				time: 2000
			});			
		}else if(password == ''){
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
				type:'post',
				url:ApiUrl+"/index.php?act=mz_login&op=phone_set_password",	
				data:{phone:phone,captcha:captcha,password:password},
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						$.dialog({
							content: result.datas,
							title: "ok",
							time: 1000
						});			
						window.setTimeout(function(){location.href = MzSiteUrl+'/home/login.html';},1000); 
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