<?php
/**
 * 团购
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
class mz_groupControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取分类
     */
    public function get_classOp() {
		$groupbuy_class = Model()->table('groupbuy_class')->field('class_id,class_name')->where(array('class_parent_id'=>0))->order('sort asc')->select();
        output_data(array('groupbuy_class' => $groupbuy_class));
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		
		$size = 10;
        $condition = array();
		$condition['is_vr'] = 0;
		$condition['state'] = 20;
		if($_GET['category'] == 'nextup'){
			$condition['start_time'] = array('gt', TIMESTAMP);
		}else{
			$condition['start_time'] = array('lt', TIMESTAMP);
			$condition['end_time'] = array('gt', TIMESTAMP);
		}
		$category = intval($_GET['category']);
		if($category > 0)$condition['class_id'] = $category;
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $group_list = Model('groupbuy')->field('goods_id,remark,start_time,groupbuy_price,buy_quantity,virtual_quantity')->where($condition)->order('start_time desc')->limit((($page-1)*$size).','.$size)->select();
		if($group_list){
			$model_goods = Model('goods');
			foreach($group_list as $k=>$v){
				$goods = $model_goods->getGoodsInfoByID($v['goods_id'], 'goods_image,goods_name,goods_marketprice,goods_storage');
				$goods['img_url'] = thumb($goods, 360);
				$goods['groupbuy_price'] = $v['groupbuy_price']*1;
				$goods['goods_marketprice'] *= 1;
				$lest = $v['start_time']-TIMESTAMP;
				$goods['timer']['hour'] = floor($lest%86400/3600);
				$goods['timer']['minute'] = floor($lest%3600/60);
				//$goods['discount'] = sprintf('%0.1f', $vv['goods_price']/$vv['goods_marketprice']*10);
				$group_list[$k] = array_merge($v,$goods);
			}
		}

        output_data(array('group_list' => $group_list));
    }


    /**
     * 获取团购列表
     */
    public function get_oversea_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_vr'] = 0;
		$condition['state'] = 20;
		$condition['goods_type'] = array('gt', 0);
        $type = intval($_GET['type']);
		if($type == '1'){
			$condition['start_time'] = array('gt', TIMESTAMP);
		}else{
			$condition['start_time'] = array('lt', TIMESTAMP);
			$condition['end_time'] = array('gt', TIMESTAMP);
		}
		if($type == '2'){
			$condition['end_time'] = array('lt', strtotime("today")+86400);
		}elseif($type == '3'){
			$size = 2;
		}

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $group_list = Model('groupbuy')->field('goods_id')->where($condition)->order('start_time desc')->limit((($page-1)*$size).','.$size)->select();
		if($group_list){
			//国家
        	$country_list = rkcache('country');
			$model_goods = Model('goods');
			foreach($group_list as $k=>$v){
				$goods = $model_goods->getGoodsInfoByID($v['goods_id'], 'country_id,goods_promotion_price,goods_jingle,goods_image,goods_name,goods_marketprice,goods_storage');
				$v['img_url'] = thumb($goods, 360);
				$v['soon'] = $type==1 ? 1 : 0;
				$v['country_icon'] = $country_list[$goods['country_id']]['country_img_url'];
				$group_list[$k] = array_merge($v,$goods);
			}
		}

        output_data(array('group_list' => $group_list));
    }
}
