var favorite = {
	view_item: "goods",
	template_list: "tmpl_list_goods",
	container : ".view_item",
	goods_page:1,goods_stop:false,
	store_page:1,store_stop: false,
	init:function(){
        set_title("我的收藏");
        this.ajax_getdata();
        this.bindEvent();
	},
	ajax_getdata: function(){
        if(stop) return;
        getAjaxResult(getUrl('mz_member_favorites','getList',"fav_type="+ this.view_item +"&page=" + page),this.template_list,this.container+ " .container_list","tmpl_empty");
	},
	bindEvent:function(){
		var a = this;
        $(".view_name span").tap(function(){
        	a.view_item = $(this).attr("view");
        	switch(a.view_item){
				case "goods":
					a.store_page = page;
					a.store_stop = stop;
					page = a.goods_page;
					stop = a.goods_stop;
					$(".container_main").removeClass("select_event");
					$(".container_main").addClass("select_item");
					a.template_list = "tmpl_list_goods";
					a.container = ".view_item";
					break;
				case "store":
					a.goods_page = page;
					a.goods_stop = stop;
					page = a.store_page;
					stop = a.store_stop;
					$(".container_main").removeClass("select_item");
					$(".container_main").addClass("select_event");
					a.template_list = "tmpl_list_store";
					a.container = ".view_event";
					break;
			}
			a.ajax_getdata();
        });
        $(".btn-edit").tap(function(){
			function setselect(b) {
				var c = $(".container_main");
				$(".target_item").removeClass("select");
				c.hasClass("select_item") ? b ? $(".view_item").addClass("flag_edit") : $(".view_item").removeClass("flag_edit") : c.hasClass("select_event") && (b ? $(".view_event").addClass("flag_edit") : $(".view_event").removeClass("flag_edit"));
			}
			switch ($(this).text()) {
			case "编辑":
				$(this).text("完成");
				setselect(true);
				break;
			case "完成":
				$(this).text("编辑");
				setselect(false);
				break;
			}
        });
        var m = $(this.container);
		m.on('click','.target_item',function(e){
			if(m.hasClass("flag_edit")){
				$(this).toggleClass("select");
				e.preventDefault();
			}
		});
		m.find(".btn_del").tap(function(){
			favorite.delete_fav();
		});
	},
	delete_fav: function(){
		var select_item = $(this.container).find(".select");
		if (0 === select_item.size()) return $.dialog({content:"请选择要删除的商品",title: "alert",time: 1000});;
		var e = [];
		select_item.each(function() {
			e.push($(this).attr("pid"))
		});
		ajax_do(getUrl('mz_member_favorites','deleteFavorites'),{ids:e.join(",")});
	}
};
favorite.init();
$(window).scroll(function() {
    if($('.loading').offset().top < $(window).scrollTop() + 1.3*$(window).height()){
        if(!stop){
            $(".loading").show();
            favorite.ajax_getdata();
            stop = true;
        }
    }
    if($('body').scrollTop() > 2000){
        $('.backtop').show();
    }else{
        $('.backtop').hide();
    }
});
$('.backtop').tap(function(){$('body').scrollTop(0);}); 