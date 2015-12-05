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
    /**
     * 获取收藏列表
     * @return [type] [description]
     */
    public function getListOp(){
        $fav_type = $_GET["fav_type"];
        $size = 10;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        if (!in_array($fav_type, array('goods','store'))) {
            output_error("未知收藏类型");
        }
        $favorites_model = Model('favorites');
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $condition['fav_type'] = $fav_type;
        $favorites_list = $favorites_model->getFavoritesList($condition,"*",true , 'log_id desc', (($page-1)*$size).','.$size);
        if (!empty($favorites_list)) {
            foreach ($favorites_list as $key => $value) {
                if ($fav_type == 'goods') {
                    $extends_goods =  Model('goods')->getGoodsInfo(array('goods_id'=>$value['fav_id']),'*');
					if($extends_goods['goods_promotion_type'] > 0){
						$extends_goods['goods_price'] = $extends_goods['goods_promotion_price'];
					}elseif($extends_goods['distribution_price'] > 0){
						$extends_goods['goods_price'] = $extends_goods['distribution_price'];
					}
                    $favorites_list[$key]['goods_img'] = thumb($extends_goods, 360);
                    $favorites_list[$key]['goods_state'] = $extends_goods['goods_state'];
                    $favorites_list[$key]['is_presell'] = $extends_goods['is_presell'];
                    $favorites_list[$key]['goods_type'] = $extends_goods['goods_type'];
                    $favorites_list[$key]['goods_price'] = $extends_goods['goods_price'];
                    $favorites_list[$key]['goods_marketprice'] = $extends_goods['goods_marketprice'];
                    $favorites_list[$key]['goods_discount'] = round($extends_goods['goods_price'] / $extends_goods['goods_marketprice'] * 10,1);
                }
            }
        }
        
        $fav_count = Model()->table('favorites')->where($condition)->count();
        $data_info = array();
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($fav_count / $size);
        output_data(array('data'=>$favorites_list,'data_info'=>$data_info));
    }
    public function deleteFavoritesOp(){
        $ids = $_POST['ids'];
        $favorites_model = Model('favorites');
        $condition = array();
        $condition['log_id'] = array("in",explode(",", $ids));
        $condition['member_id'] = $this->member_info['member_id'];
        if($favorites_model->delFavorites($condition)){
            output_data("删除收藏成功");
        }else{
            output_error("系统错误");
        }
    }
}
