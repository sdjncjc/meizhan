var mz_verify = {
	view_page:"list",
	init:function(){
		set_title("实名认证");
    	getAjaxResult(getUrl('mz_member','getUserInfo'),'','','',"mz_verify.setuserinfo");
	},
	setuserinfo:function(data){
		if (data.member_mobile_bind == 1) {
        	$(".mobile-inner").find(".answer").text("已绑定");	
		}else{
        	$(".mobile-inner").find(".answer").text("未绑定");
		}
        $(".truename-inner").find(".answer").text(data.member_truename);
        $(".idcard-inner").find(".answer").text(data.member_idnum);
        this.bindAsynEvent();
	},
	bindAsynEvent: function(){
		var page = this.view_page;
	    $(".mobile").tap(function(){
	    	$(".navbar").find(".title").text("绑定手机号码");
	    	$(".edit-mobile-page").show();
	    	$(".uprows").hide();
	    	page = "mobile";
	    });
	    $(".truename").tap(function(){
	    	page = "truename";
	    });
	    $(".idcard").tap(function(){
	    	page = "idcard";
	    });
	    $(".navbar").on("click")
	    $(".navbar .back-btn").tap(function(e){
	    	e.preventDefault();
	    	console.log(page);
	    	switch(page){
	    		case 'mobile':
	    			$(".edit-mobile-page").hide();
	    			break;
	    		case 'truename':
	    			$(".edit-truename-page").hide();
	    			break;
	    		case 'idcard':
	    			$(".edit-idcard-page").hide();
	    			break;
	    	}
	    	$(".uprows").show();
	    	page = "list";
	    });

	}

}
mz_verify.init();