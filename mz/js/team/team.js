var team = {
	tdist_all:"",
	cityApi: "http://www.qinqin.net/index.php?act=index&op=json_area&src=cache&callback=team.setRegion",
	// 判断是否正式加入小组
	checkTeam:function(data){
		console.log(data.type);
		if(data.type==0){
			$(".team_page").hide();
			$(".team_log_page").removeClass("hidden");
	        set_title("小组申请记录");
	        getAjaxResult(getUrl('mz_team_index','getApplyList'),'apply-list-tpl',".apply-list",'empty-apply-list-tpl');
		}else{
			$(".team_page").show();
			$(".team_log_page").addClass("hidden");
		}
	},
	// 初始化地址信息
	intiAddress:function(){
		var _this = this;
		var script = $("<script />").attr("type", "text/javascript");
		script.get(0).readyState ? script.get(0).onreadystatechange = function() {
			("loaded" == this.readyState || "complete" == this.readyState) && _this.resetSelect()
		} : script.get(0).onload = function() {
			_this.resetSelect();
		};
		script.attr("src", _this.cityApi);
		script.appendTo($("head"));
		_this.bindEvent();
	},
	// 绑定事件
	bindEvent:function(){
		var _this = this;
		$(".team_type").change(function(){
			_this.resetSelect();
		});
		$('.submitbutton').tap(function(){
			$.dialog({
				content : '确定创建小组？申请创建小组将删除之前的加入小组申请',
				title : 'ok',
				ok : function() {
					_this.createTeam();
				},
				cancel : function() {},
				lock : false
			});
		});
		$('.searchbutton').tap(function(){
			_this.searchTeam();
		});
		$(".search_reset").tap(function(){
			location.reload();
		});
	},
	// 地址库赋值
	setRegion:function(data){
		this.tdist_all = data;
	},
	// 初始化选择框
	resetSelect: function(a) {
		var _this = this;
		var provinces_arr = this.area2html("0"),
			addr = $(".address");
		addr.find(".provinces").html(provinces_arr);
		_this.fillcity_school();
		
		$(".provinces").on("change", function() {
			_this.fillcity_school();
		});
	},
	// 赋值级联下拉选框
	fillcity_school:function(){
		var citys_arr,city_options,team_type = $(".team_type").val(),province_id = $(".provinces").val();
		if (team_type == "0") {
			city_options = this.area2html(province_id);
			$(".provinces").siblings(".city_school").html(city_options);
		}else if (team_type == "1") {
			getAjaxResult(getUrl('mz_team_index','getSchoollist','province_id='+province_id),"","","","team.school2html");
		};
	},
	// 学校列表转换
	school2html:function(data){
		var options = "";
		data.forEach(function(data) {
			options += '<option value="' + data.id + '" text="' + data.school_name +'">' + data.school_name + "</option>"
		});
		$(".address").find(".city_school").html(options);
	},
	// 地区列表转换
	area2html: function(a) {
		var options = "";
		var area_arr = [];
		if ("number" == typeof a && (a = a.toString()),!a) return options;
		for (i in this.tdist_all){
			if (i === a) {
				for(j in this.tdist_all[i]){
					area_arr.push({
						id: this.tdist_all[i][j][0],
						areaname: this.tdist_all[i][j][1]
					});
				}
			}
		}
		area_arr.forEach(function(area_arr) {
			options += '<option value="' + area_arr.id + '" text="' + area_arr.areaname +'">' + area_arr.areaname + "</option>"
		});
		return options;
	},
	// 提交创建小组
	createTeam:function(){
		var params = {
			team_name:$(".team_name input").val(),
			provinceid:$(".provinces").val(),
			province:$(".provinces option").eq($(".provinces").attr("selectedIndex")).text(),
			city_school:$(".city_school option").eq($(".city_school").attr("selectedIndex")).text(),
			team_domain_name:$(".subdomain input").val(),
			team_type:$(".team_type").val(),
			team_intro:$(".team_intro textarea").val()
		};
        ajax_do(getUrl('mz_team_index','createTeam'),params);
	},
	// 搜索小组
	searchTeam:function(){
		var team_type = $(".team_type").val(),params = "team_type="+team_type;
		params += "&provinceid=" + $(".provinces").val() + "&keywords="+$(".keywords").val();
		params += "&city_school="+$(".city_school option").eq($(".city_school").attr("selectedIndex")).text();
		var search_html = "<span>" + $(".team_type option").eq($(".team_type").attr("selectedIndex")).text() + "</span>"
						+ "<span>" + $(".provinces option").eq($(".provinces").attr("selectedIndex")).text() + "</span>"
						+ "<span>" + $(".city_school option").eq($(".city_school").attr("selectedIndex")).text() + "</span>"
						+ "<span>" + $(".keywords").val() + "</span>";
		$(".search_item").html(search_html);
		getAjaxResult(getUrl('mz_team_index','searchTeamList',params),"team-list-tpl",".teams","empty-team-list-tpl",'team.show');
	},
	show:function(data){
		var _this = this;
		$(".search-form-page").hide();
		$(".team-list-page").removeClass("hidden");
		$(".join").tap(function(){
			_this.joinTeam($(this).attr('data-team-id'));
		});
	},
	// 加入小组
	joinTeam:function(team_id){
		ajax_do(getUrl('mz_team_index','joinTeam',"team_id="+team_id));
	}
};