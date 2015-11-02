<?php
/**
 * 用户收藏
 *
 *
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');
class mz_member_favoritesControl extends mobileMemberControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit();
    }

    /**
     * 添加收藏
     */
    public function favorites_addOp(){
        $goods_id = intval($_POST['goods_id']);
        if ($goods_id <= 0){
            output_error('参数错误');
        }

        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id'=>$goods_id,'fav_type'=>'goods','member_id'=>$this->member_info['member_id']));
        if(!empty($favorites_info)) {
            output_error('您已经收藏了该商品');
        }

        //判断商品是否为当前会员所有
        $goods_model = Model('goods');
        $goods_info = $goods_model->getGoodsInfoByID($goods_id);
        $seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
        if ($goods_info['store_id'] == $seller_info['store_id']) {
            output_error('您不能收藏自己发布的商品');
        }

        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['member_name'] = $this->member_info['member_name'];
        $insert_arr['fav_id'] = $goods_id;
        $insert_arr['fav_type'] = 'goods';
        $insert_arr['fav_time'] = TIMESTAMP;
        $result = $favorites_model->addFavorites($insert_arr);

        if ($result){
            //增加收藏数量
            $goods_model->editGoodsById(array('goods_collect' => array('exp', 'goods_collect + 1')), $goods_id);
            output_data('收藏成功');
        }else{
            output_error('收藏失败');
        }
    }

}
