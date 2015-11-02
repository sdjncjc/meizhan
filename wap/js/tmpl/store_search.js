$(function() {

    var storeId = GetQueryString("store_id");

    // 初始化页面
    var initPage = function() {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=store&op=store_goods_class",
            data: {store_id: storeId},
            dataType: 'json',
            success: function(result) {
                var data = result.datas;
                data.WapSiteUrl = WapSiteUrl;

                var title = data.store_info.store_name + ' - 店内搜索';
                document.title = title;
                $('#header h2').html(title);

                var html = template.render('store-search-tpl', data);
                $("#store-search-wrapper").html(html);

                $("#store-search-wrapper [data-toggle]").each(function() {
                    var shown = false;
                    var id = $(this).attr('data-toggle');
                    console.log(id);
                    var $items = $("[data-toggle-items='"+id+"']");
                    console.log($items.size());
                    if ($items.size() < 1) {
                        $(this).remove();
                        return;
                    }
                    $items.hide();
                    $(this).click(function() {
                        if (shown) {
                            $items.hide();
                        } else {
                            $items.show();
                        }
                        shown = !shown;
                    });
                });

            }
        });
    };

    initPage();

});
