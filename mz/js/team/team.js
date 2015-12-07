var team = {
	tdist_all:"",
	cityApi: "http://www.qinqin.net/index.php?act=index&op=json_area&src=cache&callback=team.setRegion",
	team_info:{
		team_type:0,
		provinceid:0,
		city_school_id:0,
		team_name:"",
		team_domain_name:"",
		recommend_id:"0",
		team_intro:"",
		team_id:0
	},
	// 判断是否正式加入小组
	checkTeam:function(data){
		if(data.team_id==0){
			$(".team_page").hide();
			$(".team_log_page").removeClass("hidden");
	        set_title("小组申请记录");
	        getAjaxResult(getUrl('mz_team','getApplyList'),'apply-list-tpl',".apply-list",'empty-apply-list-tpl',"team.bindEvent");
		}else{
			$(".team_page").show();
			$(".team_log_page").addClass("hidden");
			$("#team-balance").html("&yen;" + data.extend_team_info.team_balance);
			$("#team-member-num").html(data.extend_team_info.num);
			if (data.type == 1) {
				$('.apply-member').removeClass("hidden");
				$("#apply-member-num").html(data.apply_member_num);
				$("#team-balance").parent().attr("href","javascript:open_url('balance_allot')");
			};
			$(".team_info").attr('href',"javascript:open_url('team_info','','"+data.team_id+"')");
		}
	},
	// 初始化地址信息
	intiAddress:function(data){
		var _this = this,team_info;
		var script = $("<script />").attr("type", "text/javascript");
		if (data != undefined && data.extend_team_info != '') {
			_this.team_info = data.extend_team_info;
			$(".submitbutton").attr("data-team-id",data.team_id);
			$(".deletebutton").attr("data-team-log-id",data.team_apply_id);
			$(".deletebutton").removeClass("hidden");
		}

		$(".team_type").val(_this.team_info.team_type);
		$(".team_name input").val(_this.team_info.team_name);
		$(".subdomain input").val(_this.team_info.team_domain_name);
		if (_this.team_info.recommend_id > 0) {
			$(".recommend_id input").val(_this.team_info.recommend_id);
		};
		$(".team_intro textarea").val(_this.team_info.team_intro);

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
				title : 'alert',
				ok : function() {
					_this.createTeam();
				},
				cancel : function() {},
				lock : false
			});
		});
		$('.deletebutton').tap(function(){
			$.dialog({
				content : '确定撤销创建小组申请？',
				title : 'alert',
				ok : function() {
					_this.deleteTeamApply();
				},
				cancel : function() {},
				lock : false
			});
		});
		$('.searchbutton').tap(function(){
			var team_type = $(".team_type").val(),params = "team_type="+team_type;
			params += "&provinceid=" + $(".provinces").val() + "&city_school_id="+$(".city_school").val() + "&keywords="+$(".keywords").val();
			location.href = "team_list.html?" + params;
		});
		$(".delete_apply").tap(function(){
			var app_id = $(this).attr("data-id");
			$.dialog({
				content : '确定删除申请？',
				title : 'alert',
				ok : function() {
					ajax_do(getUrl("mz_team",'deleteApply','id='+app_id));
				},
				cancel : function() {},
				lock : false
			});
		});
	},
	// 地址库赋值
	setRegion:function(data){
		this.tdist_all = data;
	},
	// 初始化选择框
	resetSelect: function(a) {
		var _this = this;
		var provinces_arr = this.area2html("0"),addr = $(".address");
		addr.find(".provinces").html(provinces_arr);
		if (_this.team_info.provinceid > 0) {
			addr.find(".provinces").val(_this.team_info.provinceid);
		};
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
			if ((this.team_info.city_school_id > 0) && (this.team_info.team_type == 0)) {
				$(".city_school").val(this.team_info.city_school_id);
			}
		}else if (team_type == "1") {
			getAjaxResult(getUrl('mz_team','getSchoollist','province_id='+province_id),"","","","team.school2html");
		};
	},
	// 学校列表转换
	school2html:function(data){
		var options = "";
		data.forEach(function(data) {
			options += '<option value="' + data.id + '" text="' + data.school_name +'">' + data.school_name + "</option>"
		});
		$(".address").find(".city_school").html(options);
		if ((this.team_info.city_school_id > 0) && (this.team_info.team_type == 1)) {
			$(".address").find(".city_school").val(this.team_info.city_school_id);
		}
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
			city_school_id:$(".city_school").val(),
			city_school:$(".city_school option").eq($(".city_school").attr("selectedIndex")).text(),
			team_domain_name:$(".subdomain input").val(),
			recommend_id:$(".recommend_id input").val(),
			team_type:$(".team_type").val(),
			team_intro:$(".team_intro textarea").val(),
			team_id:$(".submitbutton").attr("data-team-id")
		};
        ajax_do(getUrl('mz_team','createTeam'),params);
	},
	// 撤销小组申请
	deleteTeamApply:function(){
		ajax_do(getUrl("mz_team",'deleteApply','id='+$(".deletebutton").attr("data-team-log-id")));
	},
	// 搜索小组
	searchTeam:function(){
        if(stop) return;
		var params = "team_type="+GetQueryString("team_type");
		params += "&provinceid=" + GetQueryString("provinceid") + "&city_school_id="+ GetQueryString("city_school_id") + "&keywords="+ GetQueryString("keywords");
		getAjaxResult(getUrl('mz_team','searchTeamList',params + "&page=" + page),"team-list-tpl",".teams","empty-team-list-tpl",'team.show');
	},
	show:function(data){
		var _this = this;
		$(".join").tap(function(){
			_this.joinTeam($(this).attr('data-team-id'));
		});
	},
	// 加入小组
	joinTeam:function(team_id){
		ajax_do(getUrl('mz_team','joinTeam',"team_id="+team_id));
	},
	// 获取小组收入日志
	getTeamBalanceLog:function(type){
        if(stop) return;
		getAjaxResult(getUrl('mz_team','getTeamBalanceLog','type='+type),"team-list-tpl",".balance_log","empty-team-list-tpl");
	}
};