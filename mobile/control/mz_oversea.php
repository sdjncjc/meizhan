<?php
/**
 * 海外购
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
class mz_overseaControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_open'] = 1;
		$condition['is_oversea'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $oversea_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($oversea_list){
			//国家
        	$country_list = rkcache('country');
			$t = strtotime("today");
			foreach($oversea_list as $k=>$v){
				$oversea_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$oversea_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				$info = unserialize($v['info']);
				$oversea_list[$k]['count'] = $info['over_cate'][0]['num'];
				if(!$v['area_id'])$v['area_id'] = 32;
				$oversea_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
				$oversea_list[$k]['country_name'] = $country_list[$v['country_id']]['country_name'];
			}
		}

        output_data(array('oversea_list' => $oversea_list));
    }

    /**
     * 获取团购列表
     */
    public function get_groupOp() {
		$size = 10;
        $condition = array();
		$condition['groupbuy.is_vr'] = 0;
		$condition['groupbuy.state'] = 20;
		$condition['goods.goods_type'] = array('gt', 0);
        $type = intval($_GET['type']);
		if($type == '1'){
			$condition['groupbuy.start_time'] = array('gt', TIMESTAMP);
		}else{
			$condition['groupbuy.start_time'] = array('lt', TIMESTAMP);
			$condition['groupbuy.end_time'] = array('gt', TIMESTAMP);
		}
		if($type == '2'){
			$t = strtotime("today");
			$condition['groupbuy.end_time'] = array('lt', $t+86400);
		}elseif($type == '3'){
			$size = 2;
		}

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
		$group_list = Model()->table('groupbuy,goods,brandsale')->field('brandsale.country_id,goods.goods_image,goods.goods_name,goods.goods_marketprice,goods.goods_promotion_price,goods.goods_storage,goods.goods_id,goods.goods_jingle')->join('inner,left')->on('groupbuy.goods_id=goods.goods_id,brandsale.brand_id=goods.brand_id,brandsale.gc_id=goods.gc_id_1')->where($condition)->order('groupbuy.start_time desc')->limit((($page-1)*$size).','.$size)->select();
		if($group_list){
			//国家
        	$country_list = rkcache('country');
			foreach($group_list as $k=>$v){
				$group_list[$k]['img_url'] = thumb($v, 360);
				$group_list[$k]['soon'] = $type==1 ? 1 : 0;
				$group_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
			}
		}

        output_data(array('group_list' => $group_list));
    }

    /**
     * 获取品牌
     */
    public function get_brandOp() {
        $condition = array();
		$condition['is_open'] = 1;
		$condition['is_oversea'] = 1;
		$condition['gc_id'] = intval($_GET['gc_id']);
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);

        $brand_list = Model('brandsale')->field('rec_id,brand_name,brand_pic')->where($condition)->order('sort desc')->limit(8)->select();
		if($brand_list){
			foreach($brand_list as $k=>$v){
                $brand_list[$k]['brand_pic'] = brandImage($v['brand_pic']);
			}
		}

        output_data(array('brand_list' => $brand_list));
    }

    /**
     * 获取分类
     */
    public function get_class2Op() {
		$gc_id = intval($_GET['gc_id']);
		$class = Model('goods_class')->field('gc_name')->where(array('gc_id'=>$gc_id))->find();
		$goods_class = Model('goods_class')->field('gc_id,gc_name')->where(array('gc_parent_id'=>$gc_id))->order('gc_sort asc')->select();
        output_data(array('class_name' => $class['gc_name'],'goods_class' => $goods_class));
    }

    /**
     * 获取商品列表
     */
    public function get_goods_listOp() {
		$size = 10;
		$condition = array();
		$condition['goods.gc_id_1'] = intval($_GET['gc_id_1']);
		$condition['goods.goods_type'] = array('gt', 0);
		$gc_id_2 = intval($_GET['gc_id_2']);
		if($gc_id_2)$condition['goods.gc_id_2'] = $gc_id_2;

		$page = intval($_GET['page']);
		$page = $page <= 0 ? 1 : $page;

		$goods_list = Model()->table('goods,brandsale')->field('goods.country_id,goods.goods_id,goods.goods_storage,goods.goods_addtime,goods.goods_salenum,goods.goods_name,goods.goods_image,goods.goods_marketprice,goods.goods_promotion_price')->join('left')->on('brandsale.brand_id=goods.brand_id,brandsale.gc_id=goods.gc_id_1')->where($condition)->group('goods.goods_commonid')->order('goods.goods_id desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			//特卖国家
        	$country_list = rkcache('country');
			$t = strtotime("today")-86400*3;
			foreach($goods_list as $k=>$v){
				$goods_list[$k]['img_url'] = thumb($v, 360);
				if($v['goods_addtime']>=$t){
					$goods_list[$k]['tag'] = 'is_new';
				}elseif($v['goods_salenum']>=100){
					$goods_list[$k]['tag'] = 'is_hot';
				}
				$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
				$goods_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 获取详情
     */
    public function get_infoOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'is_oversea'=>1,'rec_id'=>$rec_id))->find();
		if(empty($brandsale)){
            output_error('该品牌特卖未开启或不存在！');
		}
		$brandsale['image_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$brandsale['image'];
		//国家
        $country_list = rkcache('country');
		$brandsale['area_name'] = $country_list[$brandsale['country_id']]['country_name'];

        output_data(array('brandsale' => $brandsale));
    }

    /**
     * 获取商品
     */
    public function get_goodsOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'is_oversea'=>1,'rec_id'=>$rec_id))->find();
		$goods_list = array();
		if($brandsale){
			$size = 10;
			$condition = array();
			$info = unserialize($brandsale['info']);
			$condition['goods_id'] = array('in', $info['data'][0]);
			
			$page = intval($_GET['page']);
			$page = $page <= 0 ? 1 : $page;
	
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_marketprice,goods_promotion_price,country_id')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
			if($goods_list){
				//国家
				$country_list = rkcache('country');
				foreach($goods_list as $k=>$v){
					$goods_list[$k]['img_url'] = thumb($v, 360);
					$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
					$goods_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
				}
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

}
