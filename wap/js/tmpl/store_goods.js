$(function() {

    var baseUrl = ApiUrl + '/index.php?act=store&op=store_goods';

    var storeId = GetQueryString('store_id');

    $('.in-store-search').attr('href', 'store_search.html?store_id=' + storeId);

    baseUrl += '&store_id=' + storeId;
    baseUrl += '&stc_id=' + GetQueryString('stc_id');
    baseUrl += '&keyword=' + escape(GetQueryString('keyword'));
    baseUrl += '&page=' + pagesize;

    var sortKey = 1;
    var sortOrder = 2;
    var curPage = 1;
    var hasMore = false;

    var generateUrl = function() {
        return baseUrl + '&key=' + sortKey + '&order=' + sortOrder + '&curpage=' + curPage;
    };

    var doRequest = function() {
        $.ajax({
            url: generateUrl(),
            type: 'get',
            dataType: 'json',
            success: function(result) {
                hasMore = result.hasmore;
                if (hasMore){
                    $('.next-page').removeClass('disabled');
                } else {
                    $('.next-page').addClass('disabled');
                }

                var page_total = result.page_total;
                var page_html = '';
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curPage) {
                        page_html += '<option value="'+i+'" selected>'+i+'</option>';
                    } else {
                        page_html += '<option value="'+i+'">'+i+'</option>';
                    }
                }

                $('select[name=page_list]').empty().append(page_html);

                var html = template.render('home_body', result.datas);
                $("#product_list").empty().append(html);

                $(window).scrollTop(0);

                if (curPage > 1) {
                    $('.pre-page').removeClass('disabled');
                } else {
                    $('.pre-page').addClass('disabled');
                }

                if (curPage < result.page_total) {
                    $('.next-page').removeClass('disabled');
                } else {
                    $('.next-page').addClass('disabled');
                }

                $("select[name=page_list]").val('' + curPage);
            }
        });
    };

    doRequest();

    $('.keyorder').click(function() {
        // 1.销量 2.浏览量 3.价格 4.最新排序
        var curSortKey = $(this).attr('key');

        if (curSortKey == sortKey && sortOrder == 1) {
            sortOrder = 2;
        } else {
            sortOrder = 1;
        }

        sortKey = curSortKey;

        if (sortKey == 2) {
            if (sortOrder == 1) {
                $(this).find('span').removeClass('desc').addClass('asc');
            } else {
                $(this).find('span').removeClass('asc').addClass('desc');
            }
        }

        $(this).addClass("current").siblings().removeClass("current");

        doRequest();
    });

    // 跳页
    $("select[name=page_list]").change(function() {
        curPage = $(this).val();
        doRequest();
    });

    // 上一页
    $('.pre-page').click(function() {
        if (curPage <= 1) {
            return false;
        }
        curPage--;
        doRequest();
    });

    // 下一页
    $('.next-page').click(function() {
        if (!hasMore) {
            return false;
        }
        curPage++;
        doRequest();
    });

});
