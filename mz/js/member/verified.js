var mz_verify = {
	view_page:"list",
	wait:60,
	init:function(){
		set_title("实名认证");
    	getAjaxResult(getUrl('mz_member','getUserInfo'),'','','',"mz_verify.setuserinfo");
	},
	setuserinfo:function(data){
		var input = document.createElement('input');
		if (data.member_mobile_bind == 1) {
        	$(".mobile-inner").find(".answer").text("已绑定");
		}else{
        	$(".mobile-inner").find(".answer").text("未绑定");
		}
		if (data.member_idcard_bind) {
	        $(".idcard-inner").find(".answer").text("已认证");
        	if ('placeholder' in input) {
				$(".edit-idcard-page .truename input").attr("placeholder",data.member_truename);
				$(".edit-idcard-page .idcard input").attr("placeholder",data.member_idnum);
			}else{
				$(".edit-idcard-page .truename input").val(data.member_truename);
				$(".edit-idcard-page .idcard input").val(data.member_idnum);
			}
		}else{
	        $(".idcard-inner").find(".answer").text("未认证");	
		}
		if (data.member_email_bind == 1) {
	        $(".email-inner").find(".answer").text("已绑定");
		}else{
	        $(".email-inner").find(".answer").text("未绑定");
		}
        this.bindAsynEvent();
	},
	bindAsynEvent: function(){
		var _this = this;
		var page = this.view_page;
	    $(".mobile").tap(function(){
	    	open_url("auth-modify","mobile");
	    });
	    $(".email").tap(function(){
	    	open_url("auth-modify","email");
	    });
	    $(".idcard").tap(function(){
	    	$(".navbar").find(".title").text("身份认证");
	    	$(".edit-idcard-page").show();
	    	$(".uprows").hide();
	    	page = "idcard";
	    });
	    $(".navbar").on("click",".back-btn",function(e){
	    	if (page != 'list') {
	    		e.preventDefault();
	    	}
	    	switch(page){
	    		case 'idcard':
	    			$(".edit-idcard-page").hide();
	    			break;
	    		default:
	    			break;
	    	}
	    	$(".uprows").show();
	    	page = "list";
	    });
	    $(".idcard-submit").tap(function(){
			var truename = $(".edit-idcard-page .truename input").val().trim();
			var idcard = $(".edit-idcard-page .idcard input").val().trim();
			if (truename.length < 2) {
				$.dialog({
					content: '真实姓名大于两个字符',
					title: "alert",
					time: 2000
				});	
			}else{
				if (!_this.identityCodeValid(idcard)) {
					$.dialog({
						content: '身份证号码有误',
						title: "alert",
						time: 2000
					});	
				}else{
	        		ajax_do(getUrl('mz_member_verified','bind_idcard'),{truename:truename,idcard:idcard});
				}
			}
	    });

	},
    identityCodeValid:function(code){
		var city={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外 "};
        var pass= true;
        
        if(!code || !/(^\d{15}$)|(^\d{17}(\d|X)$)/i.test(code)){
            pass = false;
        }else if(!city[code.substr(0,2)]){
            pass = false;
        }else{
            //18位身份证需要验证最后一位校验位
            if(code.length == 18){
                code = code.split('');
                //∑(ai×Wi)(mod 11)
                //加权因子
                var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
                //校验位
                var parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
                var sum = 0;
                var ai = 0;
                var wi = 0;
                for (var i = 0; i < 17; i++)
                {
                    ai = code[i];
                    wi = factor[i];
                    sum += ai * wi;
                }
                var last = parity[sum % 11];
                if(parity[sum % 11] != code[17].toUpperCase())
                    pass =false;
            }
        }
        return pass;
    }

}
mz_verify.init();