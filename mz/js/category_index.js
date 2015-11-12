// JavaScript Document

$(function(){
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_category&op=index",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('cate-template', result.datas);
			$('.categories').html(html);
        }
    });
})