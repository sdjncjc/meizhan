$(function(){
	var key = getcookie('key');
	var area_list = get_area(key, 0);
	for(var i=0; i<area_list.length; i++){
		$("#area_select").find('select').append('<option value="' + area_list[i].area_id + '">' + area_list[i].area_name + '</option>');
	}
	
	$('#area_select').on('change', 'select', function(){
		$('#area_id').val($(this).val());
		var _index = $(this).index();
		$('#area_id_' + (_index + 1)).val($(this).val());
		var area_list = get_area(key, $(this).val());
		if (area_list.length == 0) {
			return false;
		}
		for (var i=_index+1; i<=4; i++) {
			$('#area_id_' + (i + 1)).val('');
			$("#area_select").find('select').eq(_index+1).remove();
		}
		
		$("#area_select").append('<select class="select-30"></select>');
		$("#area_select").find('select').last().append('<option value="">请选择...</option>');
		for (var i=0; i<area_list.length; i++) {
			$("#area_select").find('select').last().append('<option value="' + area_list[i].area_id + '">' + area_list[i].area_name + '</option>');
		}
	});

	$.sValid.init({
		rules:{
			true_name:"required",
			mob_phone:"required",
			prov_select:"required",
			city_select:"required",
			region_select:"required",
			address:"required"
		},
		messages:{
			true_name:"姓名必填！",
			mob_phone:"手机号必填！",
			prov_select:"省份必填！",
			city_select:"城市必填！",
			region_select:"区县必填！",
			address:"街道必填！"
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
	$('.add_address').click(function(){
		if($.sValid()){
			if ($('#area_select').find('select').last().val() == '') {
				$(".error-tips").html('请选择到最后一级').show();
				return false;
			}
			var true_name = $('input[name=true_name]').val();
			var mob_phone = $('input[name=mob_phone]').val();
			var tel_phone = $('input[name=tel_phone]').val();
			var city_id = $('#area_id_2').val() != '' ? $('#area_id_2').val() : $('#area_id_1').val();
			var area_id = $('#area_id').val();
			var address = $('input[name=address]').val();
			
			var area_info = '';
			if (typeof($('select').eq(0)[0]) != 'undefined') {
				area_info += $('select').eq(0)[0].options[$('select').eq(0)[0].selectedIndex].innerHTML;
			}
			if (typeof($('select').eq(1)[0]) != 'undefined') {
				area_info += ' ' + $('select').eq(1)[0].options[$('select').eq(1)[0].selectedIndex].innerHTML;
			}
			if (typeof($('select').eq(2)[0]) != 'undefined') {
				area_info += ' ' + $('select').eq(2)[0].options[$('select').eq(2)[0].selectedIndex].innerHTML;
			}
			if (typeof($('select').eq(3)[0]) != 'undefined') {
				area_info += ' ' + $('select').eq(3)[0].options[$('select').eq(3)[0].selectedIndex].innerHTML;
			}


			$.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=member_address&op=address_add",	
				data:{key:key,true_name:true_name,mob_phone:mob_phone,tel_phone:tel_phone,city_id:city_id,area_id:area_id,address:address,area_info:area_info},
				dataType:'json',
				success:function(result){
					if(result){
						location.href = WapSiteUrl+'/tmpl/member/address_list.html';
					}else{
						location.href = WapSiteUrl;
					}
				}
			});
		}
	});
});