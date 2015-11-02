<?php
/**
 * top排行榜
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
class mz_topControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    /**
     * 获取分类
     */
    public function get_classOp() {
		$top_class = Model('goods_class')->field('gc_id,gc_name')->where(array('gc_parent_id'=>0))->order('gc_sort asc')->select();
        output_data(array('top_class' => $top_class));
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['goods_state'] = 1;
		$condition['goods_verify'] = 1;
		$gc_id_1 = intval($_GET['cate']);
		if($gc_id_1)$condition['gc_id_1'] = $gc_id_1;
		$t = strtotime("today");

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
		if($gc_id_1 > 0 && $page>5)output_data(array('goods_list' => array()));
		if($page>10)output_data(array('goods_list' => array()));
		
        $goods_list = Model('goods')->field('goods_id,goods_name,goods_price,goods_storage,goods_marketprice,goods_image,brand_id')->where($condition)->order('goods_salenum desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			foreach($goods_list as $k=>$v){
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$brand = Model('brand')->field('brand_name')->where(array('brand_id'=>$v['brand_id']))->find();
				$goods_list[$k]['brand_name'] = $brand['brand_name'];
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

}
