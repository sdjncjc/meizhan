$(function(){
	var key = getcookie('key');
	if(key==''){
		location.href = 'login.html';
	}
	$.ajax({
		type:'post',
		url:ApiUrl+"/index.php?act=member_verified",
		data:{key:key},
		dataType:'json',
		//jsonp:'callback',
		success:function(result){
			checklogin(result.login);
			$('#member_truename').val(result.datas.member_info.member_truename);
			$('#member_idnum').val(result.datas.member_info.member_idnum);
			$('#member_mobile').val(result.datas.member_info.member_mobile);
			$('#vcode').val('666666');
			if(result.datas.member_info.member_mobile_bind != '1'){
				$('.send_success_tips').html('获取短信验证码');
				$('.check_mobile').show();
				$('#vcode').val('');
			}
			return false;
		}
	});
	var ALLOW_SEND = true;
	function StepTimes() {
		$num = parseInt($('.send_success_tips b').html());
		$num = $num - 1;
		$('.send_success_tips b').html($num);
		if ($num <= 0) {
			ALLOW_SEND = !ALLOW_SEND;
			$('.send_success_tips').html('获取短信验证码');
		} else {
			setTimeout(StepTimes,1000);
		}
	}
	$('#send_auth_code').click(function(){
		if ($('#member_mobile').val() == '') return false;
		if (!ALLOW_SEND) return;
		ALLOW_SEND = !ALLOW_SEND;
		$.ajax({
			type:'post',
			url:ApiUrl+"/index.php?act=member_verified&op=send_modify_mobile",
			data:{key:key,mobile:$('#member_mobile').val()},
			dataType:'json',
			//jsonp:'callback',
			success:function(result){
				if (result.datas.state == 'true') {
					$('.send_success_tips').html('<b>60</b>秒后再次获取');
					setTimeout(StepTimes,1000);
				} else {
					ALLOW_SEND = !ALLOW_SEND;
					$('.send_success_tips').html('获取短信验证码');
                    $.sDialog({
                        skin:"red",
                        content:result.datas.msg,
                        okBtn:false,
                        cancelBtn:false
                    });
				}
			}
		});
	});
	
	$.sValid.methods.truename = function(value, element) {
		var length = $.trim(value).length;
		return this.optional(element) || (length >= 2 && length <= 20);
	}; 
	$.sValid.methods.idnum = function(value, element) {
		return this.optional(element) || (/(^\d{15}$)|(^\d{17}([0-9]|X)$)/.test(value));
	}; 
	$.sValid.init({
        rules:{
			member_truename : {
				required : true,
				truename : true
			},
			member_idnum : {
				required : true,
				idnum : true
			},
			mobile : {
				required    : true,
				minlength   : 11,
				maxlength   : 11,
				digits      : true
			},
			vcode : {
				required : true,
				minlength   : 6,
				maxlength   : 6,
				digits : true
			}
        },
        messages:{
			member_truename : {
				required  : '请填写真实姓名',
				truename : '请填写正确的真实姓名'
			},
			member_idnum : {
				required : '请填写身份证号',
				idnum : '请填写正确的身份证号'
			},
			mobile : {
				required    : '请填写手机号码',
				minlength : '请填写正确的手机号码',
				maxlength : '请填写正确的手机号码',
				digits      : '请填写正确的手机号码'
			},
			vcode : {
				required : '请正确输入手机校验码',
				minlength : '请正确输入手机校验码',
				maxlength : '请正确输入手机校验码',
				digits : '请正确输入手机校验码'
			}
        },
        callback:function (eId,eMsg,eRules){
            if(eId.length >0){
                var errorHtml = "";
                $.map(eMsg,function (idx,item){
                    errorHtml += "<p>"+idx+"</p>";
                });
                $(".error-tips").html(errorHtml).show();
            }else{
                 $(".error-tips").html("").hide();
            }
        }  
    });
	$('#verifiedbtn').click(function(){//实名认证
		var member_truename = $('#member_truename').val();
		var member_idnum = $('#member_idnum').val();
		var member_mobile = $('#member_mobile').val();
		var vcode = $('#vcode').val();
		if($.sValid()){
	          $.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=member_verified&op=verified_submit",	
				data:{key:key,member_truename:member_truename,member_idnum:member_idnum,member_mobile:member_mobile,vcode:vcode},
				dataType:'json',
				success:function(result){
					if(!result.datas.msg){
						$(".error-tips").hide();
						$.sDialog({
                            skin:"red",
							content: '实名认证成功',
							okBtn:false,
							cancelBtnText:'返回',
							cancelFn: function() { window.top.close() }
						});
					}else{
						$(".error-tips").html(result.datas.error).show();
					}
				}
			 });  
        }
	});
});