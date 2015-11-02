$(function() {

    var key = getcookie('key');
    var storeId = GetQueryString("store_id");

    var isFavor = false;
    var numFavor = 0;

    var onResize = function() {
        var w = $('.store-top-header').width();
        if (w != 640) {
            var h = w * 200 / 640;
            $('.store-top-header').height(h);
        }
    };

    $(window).resize(onResize);

    // 初始化页面
    var initPage = function() {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=store&op=store_info",
            data: {key: key, store_id: storeId},
            dataType: 'json',
            success: function(result) {
                var data = result.datas;
                data.WapSiteUrl = WapSiteUrl;

                var title = data.store_info.store_name + ' - 店铺首页';
                document.title = title;
                $('#header h2').html(title);

                isFavor = data.store_info.is_favorate;
                numFavor = parseInt(data.store_info.store_collect) || 0;

                var mbTitleImg = data.store_info.mb_title_img;

                var html = template.render('store-tpl', data);
                $("#store-wrapper").html(html);

                $('.adv_list').each(function() {
                    if ($(this).find('.item').length < 2) {
                        return;
                    }

                    Swipe(this, {
                        startSlide: 2,
                        speed: 400,
                        auto: 3000,
                        continuous: true,
                        disableScroll: false,
                        stopPropagation: false,
                        callback: function(index, elem) {},
                        transitionEnd: function(index, elem) {}
                    });
                });

                if (mbTitleImg) {
                    $('#store-wrapper .store-top-header').css('background-image', 'url('+mbTitleImg+')');
                    onResize();
                }

                $("a[data-type='favor-me']").click(function() {
                    if (key == '') {
                        checklogin(0);
                        return;
                    }

                    var $this = $(this);

                    var url;
                    if (isFavor) {
                        url = ApiUrl + '/index.php?act=member_favorites_store&op=favorites_del';
                    } else {
                        url = ApiUrl + '/index.php?act=member_favorites_store&op=favorites_add';
                    }

                    $.ajax({
                        type: 'post',
                        url: url,
                        data: {key: key, store_id: storeId},
                        dataType: 'json',
                        success: function(fData) {
                            if (checklogin(fData.login)) {
                                if (!fData.datas.error) {
                                    $.sDialog({
                                        skin: "green",
                                        content: isFavor ? "已取消收藏！" : "收藏成功！",
                                        okBtn: false,
                                        cancelBtn: false
                                    });

                                    isFavor = !isFavor;

                                    if (isFavor) {
                                        numFavor++;
                                    } else {
                                        numFavor--;
                                    }

                                    $this.html(isFavor ? '取消收藏' : '收藏');
                                    $('#store-num-favor').html(numFavor);

                                } else {
                                    $.sDialog({
                                        skin: "red",
                                        content: fData.datas.error,
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                }
                            }
                        }
                    });
                });

            }
        });
    };

    initPage();

});
