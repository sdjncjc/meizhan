<?php
/**
 * 店铺
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');
class storeControl extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 店铺信息
     */
    public function store_infoOp()
    {
        $store_id = (int) $_REQUEST['store_id'];

        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }

        $store_info = array();
        $store_info['store_id'] = $store_online_info['store_id'];
        $store_info['store_name'] = $store_online_info['store_name'];

        // 店铺头像
        $store_info['store_avatar'] = $store_online_info['store_avatar']
            ? UPLOAD_SITE_URL.'/'.ATTACH_STORE.'/'.$store_online_info['store_avatar']
            : UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_store_avatar');

        // 商品数
        $store_info['goods_count'] = (int) $store_online_info['goods_count'];

        // 店铺被收藏次数
        $store_info['store_collect'] = (int) $store_online_info['store_collect'];

        // 如果已登录 判断该店铺是否已被收藏
        if ($memberId = $this->getMemberIdIfExists()) {
            $c = (int) Model('favorites')->getStoreFavoritesCountByStoreId($store_id, $memberId);
            $store_info['is_favorate'] = $c > 0;
        } else {
            $store_info['is_favorate'] = false;
        }

        // 是否官方店铺
        $store_info['is_own_shop'] = (bool) $store_online_info['is_own_shop'];

        // 动态评分
        if ($store_info['is_own_shop']) {
            $store_info['store_credit_text'] = '官方店铺';
        } else {
            $store_info['store_credit_text'] = sprintf(
                '描述: %0.1f, 服务: %0.1f, 物流: %0.1f',
                $store_online_info['store_credit']['store_desccredit']['credit'],
                $store_online_info['store_credit']['store_servicecredit']['credit'],
                $store_online_info['store_credit']['store_deliverycredit']['credit']
            );
        }

        // 页头背景图
        $store_info['mb_title_img'] = $store_online_info['mb_title_img']
            ? UPLOAD_SITE_URL.'/'.ATTACH_STORE.'/'.$store_online_info['mb_title_img']
            : '';

        // 轮播
        $store_info['mb_sliders'] = array();
        $mbSliders = @unserialize($store_online_info['mb_sliders']);
        if ($mbSliders) {
            foreach ((array) $mbSliders as $s) {
                if ($s['img']) {
                    $s['imgUrl'] = UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.$s['img'];
                    $store_info['mb_sliders'][] = $s;
                }
            }
        }

        $goods_fields = $this->getGoodsFields();
        $goods_list = (array) Model('goods')->getGoodsOnlineList(array(
            'store_id' => $store_id,
            'goods_commend' => 1,
            // 默认不显示预订商品
            'is_book' => 0,
        ), $goods_fields, 0, 'goods_id desc', 20);

        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array(
            'store_info' => $store_info,
            'rec_goods_list_count' => count($goods_list),
            'rec_goods_list' => $goods_list,
        ));
    }

    /**
     * 店铺商品分类
     */
    public function store_goods_classOp()
    {
        $store_id = (int) $_REQUEST['store_id'];

        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }

        $store_info = array();
        $store_info['store_id'] = $store_online_info['store_id'];
        $store_info['store_name'] = $store_online_info['store_name'];

        output_data(array(
            'store_info' => $store_info,
            'store_goods_class' => Model('store_goods_class')->getStoreGoodsClassPlainList($store_id),
        ));
    }

    /**
     * 店铺商品
     */
    public function store_goodsOp()
    {
        $store_id = (int) $_REQUEST['store_id'];
        $stc_id = (int) $_REQUEST['stc_id'];
        $keyword = trim((string) $_REQUEST['keyword']);

        $condition = array();
        $condition['store_id'] = $store_id;

        // 默认不显示预订商品
        $condition['is_book'] = 0;

        if ($stc_id > 0){
            $condition['goods_stcids'] = array('like', '%,' . $stc_id . ',%');
        }
        if ($keyword != '') {
            $condition['goods_name'] = array('like', '%'.$keyword.'%');
        }

        // 排序
        $order = (int) $_REQUEST['order'] == 1 ? 'asc' : 'desc';
        switch (trim($_GET['key'])) {
            case '1':
                $order = 'goods_id '.$order;
                break;
            case '2':
                $order = 'goods_promotion_price '.$order;
                break;
            case '3':
                $order = 'goods_salenum '.$order;
                break;
            case '4':
                $order = 'goods_collect '.$order;
                break;
            case '5':
                $order = 'goods_click '.$order;
                break;
            default:
                $order = 'goods_id desc';
                break;
        }

        $model_goods = Model('goods');

        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $goods_fields, $order, $this->page);
        $page_count = $model_goods->gettotalpage();

        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array(
            'goods_list_count' => count($goods_list),
            'goods_list' => $goods_list,
        ), mobile_page($page_count));
    }

    private function getGoodsFields()
    {
        return implode(',', array(
            'goods_id',
            'goods_commonid',
            'store_id',
            'goods_name',
            'goods_price',
            'goods_promotion_price',
            'goods_promotion_type',
            'goods_marketprice',
            'goods_image',
            'goods_salenum',
            'evaluation_good_star',
            'evaluation_count',
            'is_virtual',
            'is_presell',
            'is_fcode',
            'have_gift',
        ));
    }

    /**
     * 处理商品列表(团购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $goodsid_array = array();
        foreach($goods_list as $key => $value) {
            $goodsid_array[] = $value['goods_id'];
        }

        $sole_array = Model('p_sole')->getSoleGoodsList(array('goods_id' => array('in', $goodsid_array)));
        $sole_array = array_under_reset($sole_array, 'goods_id');

        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['sole_flag']      = false;
            $goods_list[$key]['group_flag']     = false;
            $goods_list[$key]['xianshi_flag']   = false;
            if (!empty($sole_array[$value['goods_id']])) {
                $goods_list[$key]['goods_price'] = $sole_array[$value['goods_id']]['sole_price'];
                $goods_list[$key]['sole_flag'] = true;
            } else {
                $goods_list[$key]['goods_price'] = $value['goods_promotion_price'];
                switch ($value['goods_promotion_type']) {
                    case 1:
                        $goods_list[$key]['group_flag'] = true;
                        break;
                    case 2:
                        $goods_list[$key]['xianshi_flag'] = true;
                        break;
                }
            }
            //商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']);

            unset($goods_list[$key]['goods_promotion_type']);
            unset($goods_list[$key]['goods_promotion_price']);
            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

}
