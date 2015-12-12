var auth = {
	type:"",
	ALLOW_SEND:true,
	ALLOW_SEND2:true,
	wait:60,
	init:function(title){
		this.type = GetQueryString("type");
		if (this.type == "") {
			return;
		};
    	this.ALLOW_SEND = true;
    	this.wait = 60;
    	set_title(title);
    	getAjaxResult(getUrl('mz_member','getUserInfo'),'','','',"auth.setuserinfo");
	},
	setuserinfo:function(data){
		var _this = this;
		var options = "";

		if (this.type == 'applycash_add') {
			if (data.member_mobile_bind == 0 && data.member_email_bind == 0) {
				$.dialog({content:"请先绑定手机或邮箱",title:"alert",time:1000});
				window.setTimeout(function(){open_url('verified');},1000); 
			};
		};
		if (data.member_mobile_bind == 1) {
			options += '<option value="mobile">手机[' + data.member_mobile + "]</option>";
		}else{
			if (_this.type == "mobile") {
				_this.modify_mobile();
			};
		}
		if (data.member_email_bind == 1) {
			options += '<option value="email">邮箱[' + data.member_email + "]</option>";
		}else{
			if (_this.type == 'email') {
				_this.modify_email();
			};
		}

		$(".auth_type").html(options);

	    $(".pin-btn").tap(function(){
	    	_this.send_auth_code();
	    });
	    $(".next-btn").tap(function(){
			var pin_code = $(".get-mobilecode-page .pin input").val();
			if (pin_code.length > 0) {
    			getAjaxResult(getUrl('mz_auth_modify','checkCaptcha','captcha=' + pin_code),'','','',"auth.checkCaptcha");
			}else{
				$.dialog({content:"请输入安全验证码",title:"alert",time:1000});
			}
	    });
	},
	send_auth_code:function(){
		var _this = this;
		if (!_this.ALLOW_SEND) return;
		_this.ALLOW_SEND = !_this.ALLOW_SEND;
		$.ajax({
			type:'get',
			url:getUrl('mz_auth_modify','send_auth_code',"type="+ $(".auth_type").val()),
			dataType:'json',
			success:function(result){
				$.dialog({
					content: result.datas.error,
					title: "alert",
					time: 1000
				});			
				if(!result.datas.error){
					$('.pin-btn').attr('disabled',true);
					_this.r_time('.pin-btn');
				}else{
					_this.ALLOW_SEND = !_this.ALLOW_SEND;
				}
			}
		});
	},
	send_auth_code2:function(){
		var _this = this;
		if (!_this.ALLOW_SEND2) return;
		_this.ALLOW_SEND2 = !_this.ALLOW_SEND2;
		var mobile = $(".edit-mobile-page .mobile input").val();
		var reg = /^1[3-578]\d{9}$/;
        if(mobile.length == 11 && reg.test(mobile)){
			$.ajax({
				type:'get',
				url:getUrl('mz_auth_modify','send_modify_mobile',"mobile="+ mobile),
				dataType:'json',
				success:function(result){
					$.dialog({
						content: result.datas.error,
						title: "alert",
						time: 2000
					});			
					if(!result.datas.error){
						$('.pin-btn2').attr('disabled',true);
						_this.r_time('.pin-btn2');
					}else{
						_this.ALLOW_SEND2 = !_this.ALLOW_SEND2;
					}
				}
			});			
    	}else{
			$.dialog({
				content: '请填写正确的手机号码',
				title: "alert",
				time: 2000
			});			
		}
	},
	r_time:function(element){
		var _this = this;
        if (_this.wait == 0) {  
            $(element).html('获取验证码').attr('disabled',null);        
            _this.wait = 60;  
        } else {
			$(element).html(+_this.wait+'秒后重试');
            _this.wait--;  
            setTimeout(function() {  
               _this.r_time(element);
            },  
            1000)  
        }  
	},
	checkCaptcha:function(data){
		var _this = this;
		if (data.result == 'succ') {
			switch(_this.type){
				case "mobile":
					_this.modify_mobile();
					break;
				case "email":
					_this.modify_email();
					break;
				case "pwd":
				case "paypwd":
					_this.modify_pwd();
					break;
				case "applycash_add":
					_this.applycash_add();
					break;
			}
		}else{
			$.dialog({content:data.message,title:"alert",time:1000});
		}
	},
	modify_mobile:function(){
		var _this = this;
		$(".get-mobilecode-page").hide();
		$(".edit-mobile-page").show();
	    $(".pin-btn2").tap(function(){
	    	_this.send_auth_code2();
	    });
		$(".submit-btn").tap(function(){
			var captcha1=$(".get-mobilecode-page .pin input").val(),captcha2=$(".edit-mobile-page .pin2 input").val(),mobile=$(".edit-mobile-page .mobile input").val();
			ajax_do(getUrl('mz_auth_modify','modify_mobile'),{captcha1:captcha1,captcha2:captcha2,mobile:mobile,type:_this.type});
		});
	},
	modify_email:function(){
		var _this = this;
		$(".get-mobilecode-page").hide();
		$(".edit-email-page").show();
		$(".submit-btn").tap(function(){
			var captcha=$(".get-mobilecode-page .pin input").val(),email=$(".edit-email-page .email input").val();
			ajax_do(getUrl('mz_auth_modify','modify_email'),{captcha:captcha,email:email,type:_this.type});
		});
	},
	modify_pwd:function(){
		var _this=this;
		$(".get-mobilecode-page").hide();
		$(".get-password-page").show();
		$(".submit-btn").tap(function(){
			var pwd = $(".get-password-page .pwd input").val(),repwd = $(".get-password-page .repwd input").val();
			if (pwd.length < 6 || pwd.length >20) {
				$.dialog({content:"请正确输入密码",title:"alert",time:1000});
				return;
			}
			if (pwd != repwd) {
				$.dialog({content:"两次密码输入不一致",title:"alert",time:1000});
				return;	
			};
			ajax_do(getUrl('mz_auth_modify','modifyPwd'),{captcha:$(".get-mobilecode-page .pin input").val(),password:pwd,confirm_password:repwd,type:_this.type});
			// 如果当前为修改登陆密码，清除登陆状态
			if (_this.type == "pwd") {
				delCookie("key");
			};
		});
	},
	applycash_add:function(){
		var _this = this;
		$(".get-mobilecode-page").addClass("hidden");
		$(".applycash-page").removeClass("hidden");
		$(".bank_type select").change(function(){
			if ($(this).val()==1) {
				$('.bank_name').addClass("hidden");
			}else if ($(this).val() == 2) {
				$('.bank_name').removeClass("hidden");
			};
		});
		$(".submit-btn").tap(function(){
			var params = {
				captcha:$(".get-mobilecode-page .pin input").val(),
				pdc_amount:$(".pdc_amount input").val(),
				pdc_bank_user:$(".pdc_bank_user input").val(),
				pdc_bank_name:($(".bank_type select").val() == 1)?"支付宝":$(".bank_name input").val(),
				pdc_bank_no:$(".pdc_bank_no input").val(),
				paypwd:$(".paypwd input").val(),
			}
			ajax_do(getUrl('mz_auth_modify','applycash_add'),params);
		});
	}
};