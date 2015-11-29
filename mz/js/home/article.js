// JavaScript Document

$(function(){
    var id = GetQueryString("id");
	
	//获取头部图片
    $.ajax({
        url: ApiUrl + "/index.php?act=mz_index&op=get_article&article_id="+id,
        type: 'get',
        dataType: 'json',
        success: function(result) {
			set_title(result.datas.article.article_title);
			$('.document').html(result.datas.article.article_content);
        }
    });
})