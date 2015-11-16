// JavaScript Document

$(function(){
	set_title('商品列表');
	$('body').css('background-color','#ddd');
	//获取商品分类
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_goods&op=get_goods_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
			var html = template('cate-template', result.datas);
			$('.categories').html(html);
        }
    });
})