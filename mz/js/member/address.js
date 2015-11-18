var address;
var tdist_all;
$(function(){
	address = {
		cityApi: "http://www.qinqin.net/index.php?act=index&op=json_area&src=cache&callback=address.setRegion",
		init: function(){
        	set_title("地址管理");
			document.domain = "qinqin.net";
			var addr = this;
			addr.loadAddressScript(addr.cityApi,function(){
				addr.resetSelect();
				addr.renderAddressList();
				addr.bindEvent();
			});
		},
		loadAddressScript: function(b,c){
			var d = $("<script />").attr("type", "text/javascript");
			d.get(0).readyState ? d.get(0).onreadystatechange = function() {
				("loaded" == this.readyState || "complete" == this.readyState) && c()
			} : d.get(0).onload = function() {
				c()
			}, d.attr("src", b), d.appendTo($("head"))
		},
		setRegion:function(data){
			tdist_all = data;
		},
		renderAddressList: function() {
			getAjaxResult(getUrl('mz_member_address','list'),'addresslist-template',".addresslist",'empty-addresslist-template',"address.bindAsynEvent");
		},
		bindAsynEvent: function(data) {
			var a = this;
			$(".address-item").tap(function() {
				var c, d = $(".edit-address-page"),
					e = $.parseJSON($(this).attr("data-address"));
				$(".list-address-page").hide();
				d.find(".receiver input").val(e.true_name);
				d.find(".phonenumber input").val(e.mob_phone);
				var area_arr = e.area_info.split("");
				var provinces_word = "";
				for(w in area_arr){
					if (area_arr[w].trim() === '') {
						break;
					};
					provinces_word += area_arr[w];
				}
				d.find('.address .provinces [text="' + provinces_word + '"]')[0].selected = !0;
				d.find(".cities").html(a.area2html(a.filterArea(d.find('.address .provinces').val())));
				d.find('.cities [value="' + e.city_id + '"]')[0].selected = !0;
				d.find(".counties").html(a.area2html(a.filterArea(e.city_id)));
				d.find('.counties [value="' + e.area_id + '"]')[0].selected = !0;
				d.find(".address-detail input").val(e.address);
				c = d.find(".select-default-address input");
				c[0].checked = "1" === e.is_default ? !0 : !1;
				d.find(".address-del").attr("data-aid", e.address_id);
				$(".edit-address-page").show();
			});
			$(".provinces").on("change", function() {
				var c = $(this).find("option").eq(this.options.selectedIndex),
					d = a.filterArea(c.get(0).value),
					e = "",
					f = $(this).siblings(".cities"),
					g = a.filterArea(d[0].id),
					h = "",
					i = $(this).siblings(".counties");
				e = a.area2html(d), f.html(e), h = a.area2html(g), i.html(h), 1 === d.length && f.trigger("change")
			});
			$(".cities").on("change", function() {
				var c = $(this).find("option").eq(this.options.selectedIndex),
					d = a.filterArea(c[0].value),
					e = "",
					f = $(this).siblings(".counties");
				e = a.area2html(d), f.html(e)
			});
			if (data === undefined) {
				a.resetSelect($(".add-address-page")), $(".list-address-page").hide(), $(".add-address-page").show();
			};
		},
		resetSelect: function(a) {
			var c, d, e, f = this.filterArea("0"),
				g = "",
				h = this.filterArea(f[0].id),
				i = "",
				j = this.filterArea(h[0].id),
				k = "";
			a || (a = $("body")), c = a.find(".provinces"), d = a.find(".cities"), e = a.find(".counties"), g = this.area2html(f), c.html(g), i = this.area2html(h), d.html(i), k = this.area2html(j), e.html(k)
		},
		filterArea: function(a) {
			var b = [];
			if ("number" == typeof a && (a = a.toString()),!a) return [];
			for (i in tdist_all){
				if (i === a) {
					for(j in tdist_all[i]){
						b.push({
							id: tdist_all[i][j][0],
							areaname: tdist_all[i][j][1]
						});
					}
				}
			}
			return b
		},
		area2html: function(a) {
			var b = "";
			return a.forEach(function(a) {
				b += '<option value="' + a.id + '" text="' + a.areaname +'">' + a.areaname + "</option>"
			}), b
		},

		bindEvent: function() {
			var a = this;
			$(".address-add").on("click", function() {
				a.resetSelect($(".add-address-page")), $(".list-address-page").hide(), $(".add-address-page").show()
			}), $(".J_backBtn").off("click"), $(".list-address-page .J_backBtn").on("click", function() {
				history.go(-1)
			}), $(".edit-address-page .J_backBtn").on("click", function() {
				$(".list-address-page").show(), $(".edit-address-page").hide()
			}), $(".add-address-page .J_backBtn").on("click", function() {
				$(".list-address-page").show(), $(".add-address-page").hide()
			}), $(".add-address-page .address-new").on("click", function() {
				a.addAddress()
			}), $(".edit-address-page .address-update").on("click", function() {
				a.updateAddress()
			}), $(".edit-address-page .address-del").on("click", function() {
				if(confirm("确定删除么？")){
					a.delAddress();
				}
			})
		},
		addAddress: function(){
			var  c = $(".add-address-page .provinces"),
				d = $(".add-address-page .cities"),
				e = $(".add-address-page .counties"),
				params = {
					true_name: $(".add-address-page .receiver input").val(),
					area_id: e.find("option").eq(e[0].options.selectedIndex).val(),
					city_id: d.find("option").eq(d[0].options.selectedIndex).val(),
					area_info: c.find("option").eq(c[0].options.selectedIndex).text() +  '	' + d.find("option").eq(d[0].options.selectedIndex).text() +  '	' + e.find("option").eq(e[0].options.selectedIndex).text(),
					address: $(".add-address-page .address-detail input").val(),
					mob_phone: $(".add-address-page .phonenumber input").val(),
					is_default: $(".add-address-page .select-default-address input").attr("checked") + 0,
					id: 0
				};
        	ajax_do(getUrl('mz_member_address','updateAddress'),params);
		},
		updateAddress:function(){
			var  c = $(".edit-address-page .provinces"),
				d = $(".edit-address-page .cities"),
				e = $(".edit-address-page .counties"),
				params = {
					true_name: $(".edit-address-page .receiver input").val(),
					area_id: e.find("option").eq(e[0].options.selectedIndex).val(),
					city_id: d.find("option").eq(d[0].options.selectedIndex).val(),
					area_info: c.find("option").eq(c[0].options.selectedIndex).text() +  '	' + d.find("option").eq(d[0].options.selectedIndex).text() +  '	' + e.find("option").eq(e[0].options.selectedIndex).text(),
					address: $(".edit-address-page .address-detail input").val(),
					mob_phone: $(".edit-address-page .phonenumber input").val(),
					is_default: $(".edit-address-page .select-default-address input").attr("checked") + 0,
					id: $(".edit-address-page .address-del").attr("data-aid")
				};
        	ajax_do(getUrl('mz_member_address','updateAddress'),params);
		},
		delAddress:function(){
			var params = {
				id: $(".edit-address-page .address-del").attr("data-aid")
			};
        	ajax_do(getUrl('mz_member_address','delAddress'),params);
		}

	};
	address.init();
});