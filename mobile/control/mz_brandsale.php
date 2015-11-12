<?php
/**
 * 品牌特卖
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
class mz_brandsaleControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_open'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);
		$t = strtotime("today");
		if($_GET['category'] == 'index'){
			$condition['start_time'] = array('gt', $t);
		}elseif($_GET['category'] == 'lastminute'){
			$condition['end_time'] = array('lt', $t+86400);
		}elseif($_GET['category'] > 0){
			$condition['gc_id'] = intval($_GET['category']);
		}else{
			$condition['recommended'] = 1;
			$size = 5;
		}
        $type = intval($_GET['type']);
		if($type)$condition['is_oversea'] = 1;
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $brandsale_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($brandsale_list){
			foreach($brandsale_list as $k=>$v){
				$brandsale_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$brandsale_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				$lest = $v['end_time']-TIMESTAMP;
				$brandsale_list[$k]['timer']['day'] = floor($lest/86400);
				$brandsale_list[$k]['timer']['hour'] = floor($lest%86400/3600);
				$brandsale_list[$k]['timer']['minute'] = floor($lest%3600/60);
			}
		}

        output_data(array('brandsale_list' => $brandsale_list));
    }

    /**
     * 获取详情
     */
    public function get_infoOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'rec_id'=>$rec_id))->find();
		if(empty($brandsale)){
            output_error('该品牌特卖未开启或不存在！');
		}
		$brandsale['image_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$brandsale['image'];
		if($brandsale['start_time']<=TIMESTAMP && $brandsale['end_time']>TIMESTAMP){
			$brandsale['remaining_time'] = $brandsale['end_time']-TIMESTAMP;
		}

		$goods_list = Model('goods_common')->field('gc_id,gc_name,count(*) as num')->where(array('goods_state'=>1,'goods_verify'=>1,'brand_id'=>$brandsale['brand_id'],'gc_id_1'=>$brandsale['gc_id']))->group('gc_id')->limit(false)->select();
		$brandsale_cate = array();
		$brandsale_cate[0] = array('gc_name'=>'全部','num'=>0);
		if($goods_list){
			foreach($goods_list as $v){
				$v['gc_name'] = end(explode(' &gt;',$v['gc_name']));
				$brandsale_cate[$v['gc_id']] = $v;
				$brandsale_cate[0]['num'] += $v['num'];
			}
		}

		$brandsale['brandsale_cate'] = $brandsale_cate;

        output_data(array('brandsale' => $brandsale));
    }

    /**
     * 获取商品
     */
    public function get_goodsOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'rec_id'=>$rec_id))->find();
		$goods_list = array();
		if($brandsale){
			$size = 10;
			$condition = array('goods_state'=>1,'goods_verify'=>1,'brand_id'=>$brandsale['brand_id'],'gc_id_1'=>$brandsale['gc_id']);
			$stock = intval($_GET['stock']);
			if($stock)$condition['goods_storage'] = array('gt', 0);
			$sort = intval($_GET['sort']);
			switch($sort){
				case '1':
					$order = "goods_salenum desc";
					break;
				case '2':
					$order = "goods_promotion_price asc";
					break;
				default:
					$order = "goods_id desc";
			}
			$cate = intval($_GET['cate']);
			if($cate)$where['gc_id'] = $cate;
			
			$page = intval($_GET['page']);
			$page = $page <= 0 ? 1 : $page;
	
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_marketprice,goods_promotion_price')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
			if($goods_list){
				foreach($goods_list as $k=>$v){
					$goods_list[$k]['img_url'] = thumb($v, 360);
					$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
				}
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

}
